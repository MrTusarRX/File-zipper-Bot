import telebot
import zipfile
import os
import time

API_KEY = "YOUR TELEGRAM BOT TOKEN ENTER HERE"

bot = telebot.TeleBot(API_KEY)

global messageVar
folder_name = ""

@bot.message_handler(commands=['start'])
def start(message):
    bot.reply_to(message, "👋 Hello, I am a Zip Bot.\nType /zip to begin uploading files to zip.")

@bot.message_handler(commands=['zip'])
def handle_zip(message):
    global messageVar, folder_name
    messageVar = message
    folder_name = str(message.from_user.id) + "_" + str(int(time.time()))
    os.mkdir(folder_name)
    msg = bot.send_message(chat_id=message.chat.id, text="📁 Please send the first file:")
    bot.register_next_step_handler(msg, handle_files, folder_name=folder_name)

def handle_files(message, folder_name):
    if message.document:
        print("File detected")
        file_id = message.document.file_id
        file_info = bot.get_file(file_id)
        downloaded_file = bot.download_file(file_info.file_path)
        with open(os.path.join(folder_name, message.document.file_name), 'wb') as f:
            f.write(downloaded_file)

    elif message.photo:
        print("Photo detected")
        file_id = message.photo[-1].file_id
        file_info = bot.get_file(file_id)
        downloaded_file = bot.download_file(file_info.file_path)
        filename = file_info.file_path.split("/")[-1]
        with open(os.path.join(folder_name, filename), 'wb') as f:
            f.write(downloaded_file)

    # Ask user if they want to upload more files
    keyboard = telebot.types.InlineKeyboardMarkup()
    yes_button = telebot.types.InlineKeyboardButton("➕ Upload more files", callback_data="yes")
    no_button = telebot.types.InlineKeyboardButton("✅ Create ZIP", callback_data="no")
    keyboard.add(yes_button, no_button)
    bot.send_message(chat_id=message.chat.id, text="Do you want to upload more files?", reply_markup=keyboard)

@bot.callback_query_handler(func=lambda x: True)
def callback_handler(callback_query):
    global folder_name, messageVar
    data = callback_query.data
    bot.answer_callback_query(callback_query.id)

    if data == "yes":
        msg = bot.send_message(chat_id=messageVar.chat.id, text="📤 Send another file:")
        bot.register_next_step_handler(msg, handle_files, folder_name=folder_name)
        bot.delete_message(chat_id=callback_query.message.chat.id, message_id=callback_query.message.message_id)

    elif data == "no":
        zip_file_name = folder_name + ".zip"
        with zipfile.ZipFile(zip_file_name, 'w', zipfile.ZIP_DEFLATED) as zipf:
            for file in os.listdir(folder_name):
                zipf.write(os.path.join(folder_name, file), arcname=file)

        with open(zip_file_name, 'rb') as f:
            bot.send_document(chat_id=messageVar.chat.id, document=f)

        # Cleanup
        os.remove(zip_file_name)
        for file in os.listdir(folder_name):
            os.remove(os.path.join(folder_name, file))
        os.rmdir(folder_name)
        bot.delete_message(chat_id=callback_query.message.chat.id, message_id=callback_query.message.message_id)
        bot.send_message(chat_id=messageVar.chat.id, text="✅ Your ZIP file has been created and sent!")

print("🤖 Bot is running...")
bot.polling()


