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
require '.bot/functions/jalaliToUnix.php'; // Include jalaliToUnix function

//========================  Keyboards:  ==========================
//========= keyboard_start: =========
$keyboard_start = [
    'keyboard' => [
        [['text' => "📋 لیست های انتشار"], ['text' => "➕ افزودن وظیفه"]],
        [['text' => "👥 مدیریت کاربران"], ['text' => "ℹ️ درباره ربات"]],
        [['text' => "تنظیمات ⚙️"]]
    ],
    'resize_keyboard' => true, // Resize the keyboard to fit content
    'one_time_keyboard' => true // Keep the keyboard open after a selection
];
$keyboard_start = json_encode($keyboard_start);

//========= keyboard_settings: =========
$keyboard_setting = [
    'keyboard' => [
        [['text' => "🔗 تغییر نام نمایشی"], ['text' => "🔔 اعلان‌ها"]],
        [['text' => "🔙 بازگشت به منوی اصلی"]]
    ],
    'resize_keyboard' => true, // Resize the keyboard to fit content
    'one_time_keyboard' => true // Keep the keyboard open after a selection
];
$keyboard_setting = json_encode($keyboard_setting);

//========= keyboard_list: ========= 
$keyboard_list = [
    'keyboard' => [
        [['text' => "🔔 لیست‌های عضو شده"], ['text' => "📝 مدیریت لیست‌های من"]],
        [['text' => "🔙 بازگشت به منوی اصلی"]]
    ],
    'resize_keyboard' => true, // Resize the keyboard to fit content
    'one_time_keyboard' => true // Keep the keyboard open after a selection
];
$keyboard_list = json_encode($keyboard_list);

//========= keyboard_cancel: =========
$keyboard_cancel = [
    'keyboard' => [
        [['text' => "لغو عملیات ❌"]]
    ],
    'resize_keyboard' => true, // Resize the keyboard to fit content
    'one_time_keyboard' => true // Keep the keyboard open after a selection
];
//========= keyboard_manage_list: =========
$keyboard_manage_list = [
    'keyboard' => [
        [['text' => "➕ لیست جدید"], ['text' => "🔙 بازگشت"]]
        //list appends here
    ],
    'resize_keyboard' => true, // Resize the keyboard to fit content
    'one_time_keyboard' => true // Keep the keyboard open after a selection
];
//================================================================




if ($text == "/start") {
    $text = "👋 سلام $first_name عزیز! خوش آمدید.\n\n✨ به ربات مدیریت وظایف خوش آمدید.\n\n🔹 با استفاده از این ربات می‌توانید لیست وظایف ایجاد کنید، کاربران را مدیریت کنید و یادآوری‌های مهم دریافت کنید.";
    update_step(null);
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_start
    ]);
} else if ($text == "📋 لیست های انتشار") {
    $text = "
    *لطفا انتخاب کنید* 👇
\- 📝 *مدیریت لیست‌های من*:
>میتوانید لیست ایجاد کنید و لیست های از قبل ایجاد شده توسط خودتان را مدیریت کنید\.

\- 🔔 *لیست‌های من*:
>برای مشاهده و مدیریت تمام لیست هایی که شما مشترک آن هستید میتوانید استفاده کنید\.
";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => "MarkdownV2",
        'reply_markup' => $keyboard_list
    ]);
} else if ($text == "📝 مدیریت لیست‌های من") {
    //get user lists
    $db_lists = $db->q("SELECT 
    l.id,
    l.list_name,
    l.list_lastest_update,
    l.list_created_at,
    l.is_deleted,
    l.task_adding_rule
    FROM 
    tbl_notification_lists l
    JOIN 
    tbl_users u 
    ON 
    l.list_owner_id = u.id
    WHERE 
    u.tg_id = ? AND l.is_deleted = 0;", [$tg_id]);

    if (isset($db_lists[0])) {
        //if user has lists
        // Append user lists directly to the keyboard
        foreach ($db_lists as $list) {
            $keyboard_manage_list['keyboard'][] = [['text' => "📂 " . $list['list_name']]];
        }
    }
    $keyboard_manage_list = json_encode($keyboard_manage_list);

    $text = "لیست خود را انتخاب کنید یا یکی اضافه کنید:";
    update_step("choosing_list");
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_manage_list
    ]);



} else if ($user_step == "choosing_list" && (preg_match("/📂 /", $text) || $text == "🔙 بازگشت")){
    if (preg_match("/📂 /", $text)) {
        $text = str_replace("📂 ", "", $text);
        $db_list = $db->q("SELECT * FROM tbl_notification_lists WHERE list_name = ? AND list_owner_id = (SELECT id FROM tbl_users WHERE tg_id = ?)", [$text, $tg_id]);
        if (isset($db_list[0])) {
            $text = "📂 لیست $text انتخاب شد.\n\n🔹 لطفا یکی از گزینه‌های زیر را انتخاب کنید:";
            bot("sendMessage", [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => 'تغییر نام لیست ✍️', 'callback_data' => 'rename_list_'.$db_list['id']],['text' => '🗑 حذف لیست', 'callback_data' => "delete_".$db_list['id']]],
                        [['text' => 'تغییر دسترسی ایجاد یادآوری 📝', 'callback_data' => 'e_task_rule_'.$db_list['id']]],
                        [['text' => '🔙 Back', 'callback_data' => 'back_action']],
                    ]
                ]
            ]);
        } else {
            $text = "🔗 لیست $text یافت نشد.";
            bot("sendMessage", [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => $keyboard_list
            ]);
        }
    } else if ($text == "🔙 بازگشت") {
        update_step(null);
        $text = "🔙 شما به منوی قبلی بازگشتید.";
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => $keyboard_list
        ]);
    } else {
        update_step(null);
        $text = "🔗 لطفا یکی از لیست‌های خود را انتخاب کنید.";
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => $keyboard_list
        ]);
    }


} else if ($text == "➕ لیست جدید" || $user_step == "create_list") {

    if ($text == "لغو عملیات ❌") {
        update_step(null);
        $text = "🔗 عملیات لغو شد.";
        bot("sendMessage", ['chat_id' => $chat_id, 'text' => $text, 'reply_markup' => $keyboard_start]);
        exit();
    }


    if ($user_step == "create_list") {
        //create new list
        $db->q("INSERT INTO tbl_notification_lists (list_name, list_owner_id) VALUES (?, (SELECT id FROM tbl_users WHERE tg_id = ?))", [$text, $tg_id]);
        update_step(null);
        $text = "🔗 لیست جدید با نام $text ایجاد شد.";
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => $keyboard_list
        ]);
        exit();
    }


    $text = "لطفا نام لیست جدید خود را وارد کنید:";
    update_step("create_list");
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_cancel
    ]);





} else if ($text == "🔔 لیست‌های عضو شده") {


    $text = "🔔 این بخش برای مدیریت لیست‌های شما طراحی شده است.\n\n🔹 هنوز لیست جدیدی ایجاد نشده‌است.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
} else if ($text == "🔙 بازگشت به منوی اصلی") {
    $text = "🔙 شما به منوی اصلی بازگشتید.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_start
    ]);
} else if ($text == "➕ افزودن وظیفه") {
    $text = "✏️ لطفاً عنوان وظیفه جدید خود را وارد کنید:";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
} else if ($text == "👥 مدیریت کاربران") {
    $text = "👤 این بخش برای مدیریت کاربران شما طراحی شده است.\n\n🔹 هنوز کاربران جدیدی اضافه نشده‌اند.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
} else if ($text == "ℹ️ درباره ربات") {
    $text = "🤖 این ربات برای مدیریت وظایف و یادآوری‌ها طراحی شده است.\n\n📬 پیشنهادات و مشکلات خود را از طریق پیام ارسال کنید.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text
    ]);
} else if ($text == "تنظیمات ⚙️") {
    $text = "🔧 در این بخش می‌توانید تنظیمات ربات خود را مدیریت کنید.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_setting
    ]);
} else if ($text == "🔗 تغییر نام نمایشی" || $user_step == "change_name" || $user_step == "change_family") {

    if ($text == "لغو عملیات ❌") {
        update_step(null);
        $text = "🔗 عملیات لغو شد.";
        bot("sendMessage", ['chat_id' => $chat_id, 'text' => $text, 'reply_markup' => $keyboard_start]);
        exit();
    }


    if ($user_step == "change_name") {
        //update first name
        $db->q("UPDATE tbl_users SET first_name = ? WHERE tg_id = ?", [$text, $tg_id]);
        update_step("change_family");
        $text = "🔗 نام شما با موفقیت ثبت شد\. لطفا نام خانوادگی خود را وارد کنید\:";
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "MarkdownV2",
            'reply_markup' => $keyboard_cancel
        ]);
    } else if ($user_step == "change_family") {
        //update last name  
        $db->q("UPDATE tbl_users SET last_name = ? WHERE tg_id = ?", [$text, $tg_id]);
        update_step(null);
        $text = "🔗 نام خانوادگی شما با موفقیت ثبت شد\. تغییرات با موفقیت اعمال شد\.";
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "MarkdownV2",
            'reply_markup' => $keyboard_start
        ]);
    } else {

        $text = "🔗 لطفا نام جدید خود را وارد کنید:
>نام کاربری شما میتواند فارسی یا انگلیسی باشد و به سایر کاربران ربات نشان داده خواهد شد\.
>بهتر است از نام و نام خانوادگی خود به صورت فارسی برای این کار استفاده کنید\.
";

        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "MarkdownV2"
        ]);

        $text = "🔗 لطفا نام جدید خود را وارد کنید:";
        update_step("change_name");
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'parse_mode' => "MarkdownV2",
            'reply_markup' => $keyboard_cancel
        ]);
    }
} else if (($text == "🔙 بازگشت به منوی اصلی" || $text == "🔙 بازگشت") && $user_step == null) {
    $text = "🔙 شما به منوی اصلی بازگشتید.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_start
    ]);
} else {
    $text = "🤔 متوجه دستور شما نشدم. لطفا دوباره تلاش کنید.";
    update_step(null);
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_start
    ]);
}
