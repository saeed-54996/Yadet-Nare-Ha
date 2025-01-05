<?php

//===============  Telegram Basic Variables:  =============
$chat_id = $update["message"]['chat']['id'] ?? null;
$text = $update["message"]['text'] ?? null;
$username = $update["message"]['from']['username'] ?? null;
$first_name = $update["message"]['from']['first_name'] ?? null;
$last_name = $update["message"]['from']['last_name'] ?? null;
$tg_id = $update["message"]['from']['id'] ?? null;
$message_id = $update["message"]['message_id'] ?? null;
//===============                             =============

//===============  Include functions:  =============
require './bot/functions/init-user.php'; // Init User system on start


//========================  Keyboards:  ==========================
    //========= keyboard_start: =========
    $keyboard_start = [
        'keyboard' => [
            [['text' => "📋 لیست های انتشار"],['text' => "➕ افزودن وظیفه"]],
            [['text' => "👥 مدیریت کاربران"],['text' => "ℹ️ درباره ربات"]],
            [['text' => "تنظیمات ⚙️"]]
        ],
        'resize_keyboard' => true, // Resize the keyboard to fit content
        'one_time_keyboard' => true // Keep the keyboard open after a selection
    ];
    $keyboard_start = json_encode($keyboard_start);

    //========= keyboard_settings: =========
    $keyboard_setting = [
        'keyboard' => [
            [['text' => "🔗 تغییر نام کاربری"],['text' => "🔔 اعلان‌ها"]],
            [['text' => "🔙 بازگشت به منوی اصلی"]]
        ],
        'resize_keyboard' => true, // Resize the keyboard to fit content
        'one_time_keyboard' => true // Keep the keyboard open after a selection
    ];
    $keyboard_setting = json_encode($keyboard_setting);

    //========= keyboard_list: ========= 
    $keyboard_list = [
        'keyboard' => [
            [['text' => "🔔 لیست‌های من"],['text' => "📝 مدیریت لیست‌های من"]],
            [['text' => "🔙 بازگشت به منوی اصلی"]]
        ],
        'resize_keyboard' => true, // Resize the keyboard to fit content
        'one_time_keyboard' => true // Keep the keyboard open after a selection
    ];
    $keyboard_list = json_encode($keyboard_list);
//================================================================




if ($text == "/start") {
    $text = "👋 سلام $first_name عزیز! خوش آمدید.\n\n✨ به ربات مدیریت وظایف خوش آمدید.\n\n🔹 با استفاده از این ربات می‌توانید لیست وظایف ایجاد کنید، کاربران را مدیریت کنید و یادآوری‌های مهم دریافت کنید.";

    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_start
    ]);
}

if ($text == "📋 لیست های انتشار") {
    $text = "
    **لطفا انتخاب کنید** 👇
\- 📝 **مدیریت لیست‌های من**:
>میتوانید لیست ایجاد کنید و لیست های از قبل ایجاد شده توسط خودتان را مدیریت کنید.

\- 🔔 **لیست‌های من**:
>برای مشاهده و مدیریت تمام لیست هایی که شما مشترک آن هستید میتوانید استفاده کنید.
";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => "MarkdownV2",
        'reply_markup' => $keyboard_list
    ]);
}

if ($text == "➕ افزودن وظیفه") {
    $text = "✏️ لطفاً عنوان وظیفه جدید خود را وارد کنید:";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
}

if ($text == "👥 مدیریت کاربران") {
    $text = "👤 این بخش برای مدیریت کاربران شما طراحی شده است.\n\n🔹 هنوز کاربران جدیدی اضافه نشده‌اند.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
}

if ($text == "ℹ️ درباره ربات") {
    $text = "🤖 این ربات برای مدیریت وظایف و یادآوری‌ها طراحی شده است.\n\n📬 پیشنهادات و مشکلات خود را از طریق پیام ارسال کنید.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
}

if ($text == "تنظیمات ⚙️") {
    $text = "🔧 در این بخش می‌توانید تنظیمات ربات خود را مدیریت کنید.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_setting
    ]);
}
