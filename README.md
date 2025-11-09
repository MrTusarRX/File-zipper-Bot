# 🤖 FileZipperBot

A simple **Telegram File Zipper Bot** built using **Python (telebot)** and **PHP (cURL)**.  
It allows users to upload multiple files or photos, then bundles them into a single `.zip` archive and sends it back through Telegram.

---

## 🧩 Features

✅ Upload multiple files or images  
✅ Create and send a ZIP file directly in Telegram  
✅ Automatic cleanup after sending  
✅ Supports both **Python** and **PHP** versions  
✅ Simple to deploy — works on any basic web server or VPS  

---

## ⚙️ Installation (PHP Version)

### 1. Clone the repository
```bash
git clone https://github.com/YADAV/File-zipper-Bot.git
cd File-zipper-Bot
```

### 2. Update your bot token
Open `bot.php` and replace:
```php
$API_KEY = "YOUR_BOT_TOKEN_HERE";
```

with your actual Telegram bot token.

### 3. Upload to your web host
Place `bot.php` on your PHP-supported hosting (HTTPS required).  
For example:
```
https://your-domain.com/bot.php
```

### 4. Set the Telegram webhook
Run this in your terminal (replace values accordingly):
```bash
curl "https://api.telegram.org/botYOUR_BOT_TOKEN/setWebhook?url=https://your-domain.com/bot.php"
```

If successful, you’ll get:
```json
{"ok":true,"result":true,"description":"Webhook was set"}
```

---

## ⚙️ Installation (Python Version)

### 1. Install dependencies
```bash
pip install pyTelegramBotAPI
```

### 2. Set your API key
Edit `zip_bot.py` and replace:
```python
API_KEY = "YOUR_BOT_TOKEN_HERE"
```

### 3. Run the bot
```bash
python zip_bot.py
```

---

## 🚧 Limitations

1. **File size limit (20 MB)** — Telegram doesn’t allow sending larger files through the standard API.
2. **Global variable conflicts** — When multiple users use the bot simultaneously, sessions may overlap.  
   → Can be fixed by using user-specific sessions or a database (planned update).
3. **Single upload flow** — The current flow asks one file at a time.  
   → Could be improved to handle multiple simultaneous uploads.

---

## 💡 Planned Improvements

- [ ] Add session management per user (to support concurrent users)  
- [ ] Allow multiple files in one upload session  
- [ ] Add progress indicators (upload %, zipping status)  
- [ ] Handle >20 MB uploads via chunking or Telegram’s file_id links  
- [ ] Add Docker support for easy deployment  

---

## 🧠 How It Works

1. User sends `/zip` command  
2. Bot creates a temporary folder for that user session  
3. User uploads one or more files/photos  
4. Bot asks:  
   - “➕ Upload more files”  
   - “✅ Create ZIP”  
5. When user selects “✅ Create ZIP” →  
   The bot zips all files and sends the archive back to the user.

---

## 📂 File Structure

```
File-zipper-Bot/
├── bot.php        # PHP implementation
├── zip_bot.py     # Python implementation
├── README.md
```

---

## 👑 Credits

**Created, Designed, and Documented by:**  
### 🧑‍💻 MrTusarRX  
Telegram: [@MrTusarRX](https://t.me/MrTusarRXx)  
GitHub: [https://github.com/MrTusarRX](https://github.com/MrTusarRX)

Special thanks to **MrTusarRX** for the original inspiration and idea.

---

## 🪪 License

This project is open-source and free to use under the **MIT License**.
