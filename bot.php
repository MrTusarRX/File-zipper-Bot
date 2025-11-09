<?php
// ===============================
// 🤖 Telegram ZIP Bot in PHP
// ===============================

$API_KEY = "7746083206:AAEF7ECXceEFmDLI6VcA_Rk-ofCslkN9SYE";
$API_URL = "https://api.telegram.org/bot$API_KEY/";

function apiRequest($method, $params = [])
{
    global $API_URL;
    $url = $API_URL . $method;
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}

// ===============================
// 🧠 Bot Logic
// ===============================

$update = json_decode(file_get_contents("php://input"), true);

if (!$update) exit;

if (isset($update["message"])) {
    $message = $update["message"];
    $chat_id = $message["chat"]["id"];
    $user_id = $message["from"]["id"];

    if (isset($message["text"])) {
        $text = $message["text"];

        if ($text == "/start") {
            apiRequest("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "👋 Hello, I am a Zip Bot.\nType /zip to begin uploading files to zip."
            ]);
        }

        elseif ($text == "/zip") {
            $folder_name = $user_id . "_" . time();
            mkdir($folder_name);
            file_put_contents("session_$chat_id.txt", $folder_name);

            apiRequest("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "📁 Please send the first file to begin zipping."
            ]);
        }
    }
    elseif (isset($message["document"]) || isset($message["photo"])) {
        $folder_name = file_exists("session_$chat_id.txt") ? trim(file_get_contents("session_$chat_id.txt")) : null;

        if (!$folder_name) {
            apiRequest("sendMessage", [
                "chat_id" => $chat_id,
                "text" => "❗ Please start with /zip before sending files."
            ]);
            exit;
        }

        if (isset($message["document"])) {
            $file_id = $message["document"]["file_id"];
            $filename = $message["document"]["file_name"];
        } else {
            $photo = end($message["photo"]);
            $file_id = $photo["file_id"];
            $filename = "photo_" . uniqid() . ".jpg";
        }
        $file_info = file_get_contents("https://api.telegram.org/bot$API_KEY/getFile?file_id=$file_id");
        $file_info = json_decode($file_info, true);
        $file_path = $file_info["result"]["file_path"];
        $download_url = "https://api.telegram.org/file/bot$API_KEY/$file_path";
        file_put_contents("$folder_name/$filename", file_get_contents($download_url));
        $keyboard = [
            "inline_keyboard" => [
                [
                    ["text" => "➕ Upload more files", "callback_data" => "yes"],
                    ["text" => "✅ Create ZIP", "callback_data" => "no"]
                ]
            ]
        ];

        apiRequest("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "Do you want to upload more files?",
            "reply_markup" => json_encode($keyboard)
        ]);
    }
}

elseif (isset($update["callback_query"])) {
    $callback = $update["callback_query"];
    $chat_id = $callback["message"]["chat"]["id"];
    $data = $callback["data"];
    $message_id = $callback["message"]["message_id"];

    $folder_name = file_exists("session_$chat_id.txt") ? trim(file_get_contents("session_$chat_id.txt")) : null;

    if ($data == "yes") {
        apiRequest("deleteMessage", ["chat_id" => $chat_id, "message_id" => $message_id]);
        apiRequest("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "📤 Send another file:"
        ]);
    }

    elseif ($data == "no") {
        $zip_file = $folder_name . ".zip";
        $zip = new ZipArchive();
        if ($zip->open($zip_file, ZipArchive::CREATE) === TRUE) {
            foreach (scandir($folder_name) as $file) {
                if ($file != "." && $file != "..") {
                    $zip->addFile("$folder_name/$file", $file);
                }
            }
            $zip->close();
        }

        apiRequest("sendDocument", [
            "chat_id" => $chat_id,
            "document" => new CURLFile($zip_file)
        ]);

        // Cleanup
        unlink("session_$chat_id.txt");
        foreach (scandir($folder_name) as $file) {
            if ($file != "." && $file != "..") unlink("$folder_name/$file");
        }
        rmdir($folder_name);
        unlink($zip_file);

        apiRequest("deleteMessage", ["chat_id" => $chat_id, "message_id" => $message_id]);
        apiRequest("sendMessage", [
            "chat_id" => $chat_id,
            "text" => "✅ Your ZIP file has been created and sent!"
        ]);
    }
}
?>
