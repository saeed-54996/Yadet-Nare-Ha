<?php

//===============  Telegram Basic Variables:  =============
$chat_id = $update["message"]['chat']['id'] ?? null;
$text = $update["message"]['text'] ?? null;
$username = $update["message"]['from']['username'] ?? null;
$first_name = $update["message"]['from']['first_name'] ?? null;
$last_name = $update["message"]['from']['last_name'] ?? null;
$tg_id = $update["message"]['from']['id'] ?? null;
$message_id = $update["message"]['message_id'] ?? null;

//replied message:
$replied_message_id = $update['message']['reply_to_message']['message_id'] ?? null;
$replied_chat_id = $update['message']['reply_to_message']['chat']['id'] ?? null;
//===============                             =============

//===============  Include functions:  =============
require __DIR__ . '/../functions/init-user.php'; // Init User system on start
require __DIR__ . '/../functions/jalaliToUnix.php'; // Include jalaliToUnix function
require __DIR__ . '/../functions/global.php'; // Include global functions

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
} else if ($user_step == "choosing_list" && (preg_match("/📂 /", $text) || $text == "🔙 بازگشت")) {
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
                        [['text' => 'تغییر نام لیست ✍️', 'callback_data' => 'rename_list_' . $db_list[0]['id']], ['text' => '🗑 حذف لیست', 'callback_data' => "delete_" . $db_list[0]['id']]],
                        [['text' => 'تغییر دسترسی ایجاد یادآوری 📝', 'callback_data' => 'e_task_rule_' . $db_list[0]['id']]],
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
        //check if the list name is repeated from user list:
        $db_list = $db->q("SELECT * FROM tbl_notification_lists WHERE list_name = ? AND list_owner_id = (SELECT id FROM tbl_users WHERE tg_id = ?)", [$text, $tg_id]);
        if (isset($db_list[0])) {
            update_step(null);
            $text = "🔗 لیست $text قبلا ایجاد شده است.";
            bot("sendMessage", [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => $keyboard_list
            ]);
            exit();
        }
        //create new list
        $db->q("INSERT INTO tbl_notification_lists (list_name, list_owner_id) VALUES (?, (SELECT id FROM tbl_users WHERE tg_id = ?))", [$text, $tg_id]);
        //add user as subscriber to the list:
        $db->q("INSERT INTO tbl_list_subscribers (list_id, user_id) VALUES ((SELECT id FROM tbl_notification_lists WHERE list_name = ? AND list_owner_id = (SELECT id FROM tbl_users WHERE tg_id = ?)), (SELECT id FROM tbl_users WHERE tg_id = ?))", [$text, $tg_id, $tg_id]);
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
} else if ($text == "🔔 لیست‌های عضو شده" || $user_step == "choosing_subscribed_list") {
    if (preg_match("/📂 /", $text) && $user_step == "choosing_subscribed_list") {
        $text = str_replace("📂 ", "", $text);
        // getting all user subscribed list:
        $db_list = $db->q("SELECT * FROM tbl_notification_lists WHERE list_name = ? AND id IN (SELECT list_id FROM tbl_list_subscribers WHERE user_id = (SELECT id FROM tbl_users WHERE tg_id = ?))", [$text, $tg_id]);
        if (isset($db_list[0])) {
            $db_list = $db_list[0];
            $text = "📂 لیست $text انتخاب شد.\n\n🔹 لطفا یکی از گزینه‌های زیر را انتخاب کنید:";
            bot("sendMessage", [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => 'مشاهده وظایف 📋', 'callback_data' => 'view_tasks_' . $db_list['id']], ['text' => 'افزودن وظیفه ➕', 'callback_data' => "add_task_" . $db_list['id']]],
                        [['text' => '📦 گزینه های بیشتر', 'callback_data' => 'more_options_' . $db_list['id']]],
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
        exit();
    } else if ($text == "🔙 بازگشت") {
        update_step(null);
        $text = "🔙 شما به منوی اصلی بازگشتید.";
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => $keyboard_start
        ]);
        exit();
    }
    //get all user subscribed list and show as keyboard:
    $user_subscription = $db->q("SELECT * FROM tbl_list_subscribers sub JOIN tbl_notification_lists nlist ON sub.list_id=nlist.id WHERE user_id = (SELECT id FROM tbl_users WHERE tg_id = ?)", [$tg_id]);
    if (isset($user_subscription[0])) {
        //if user has lists
        // Append user lists directly to the keyboard
        foreach ($user_subscription as $list) {
            $keyboard_manage_list['keyboard'][] = [['text' => "📂 " . $list['list_name']]];
        }
    }

    //unset new-list keyboard button:
    array_shift($keyboard_manage_list['keyboard'][0]);
    $keyboard_manage_list = json_encode($keyboard_manage_list);
    update_step("choosing_subscribed_list");
    $text = "📣 *لیست هایی که شما عضو آن هستید* :
>میتوانید با کلیک روی لیست مورد نظر آخرین وظایف افزوده شده را مشاهده کنید یا به آن اضافه کنید\.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => "MarkdownV2",
        'reply_markup' => $keyboard_manage_list

    ]);
} else if ($text == "➕ افزودن وظیفه") {
    //get all user subscribed list and show as keyboard:
    $user_subscription = $db->q("SELECT * FROM tbl_list_subscribers sub JOIN tbl_notification_lists nlist ON sub.list_id=nlist.id WHERE user_id = (SELECT id FROM tbl_users WHERE tg_id = ?)", [$tg_id]);
    if (isset($user_subscription[0])) {
        //if user has lists
        // Append user lists directly to the keyboard
        foreach ($user_subscription as $list) {
            $keyboard_manage_list['keyboard'][] = [['text' => "📂 " . $list['list_name']]];
        }
    }

    //unset new-list keyboard button:
    array_shift($keyboard_manage_list['keyboard'][0]);
    $keyboard_manage_list = json_encode($keyboard_manage_list);
    update_step("choosing_subscribed_list");
    $text = "📣 *لیست هایی که شما عضو آن هستید* :
>میتوانید با کلیک روی لیست مورد نظر آخرین وظایف افزوده شده را مشاهده کنید یا به آن اضافه کنید\.";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'parse_mode' => "MarkdownV2",
        'reply_markup' => $keyboard_manage_list

    ]);
} else if ($user_step == "add_task") {

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
} else if (preg_match('/^(add_task_to_list)_([0-9]+)$/',$user_step,$matches)){
    $order = $matches[1];
    $list_id = $matches[2];

    $db->q("INSERT INTO tbl_tasks (task_name, list_id) VALUES (?, ?)", [$text, $list_id]);
    
    $mtext = "🔹بسیار عالی\!\!  
ادامه گام‌های زیر رو به ترتیب برای افزودن وظیفه جدید طی می‌کنیم 👇  
~🟢 **گام 1**: افزودن نام برای وظیفه  ~
🟡 **گام 2**: افزودن توضیحات وظیفه  
⚪️ **گام 3**: افزودن تاریخ وظیفه  
\_\_\_  
> 🔵 **توضیحات اضافه مربوط به وظیفه خود را وارد کنید**\.
    ";
    $task = $db->q("SELECT * FROM tbl_tasks WHERE task_name = ? AND list_id = ? ORDER BY id DESC LIMIT 1",[$text,$list_id]);
    update_step("add_des_to_task_".$task[0]['id']."_".$list_id);
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $mtext,
            'parse_mode' => "MarkdownV2",
            'force_reply' => true,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🔙 لغو و بازگشت', 'callback_data' => 'view_list_' . $list_id]],
                ]
            ]
        ]);

} else if (preg_match('/^(add_des_to_task)_([0-9]+)_([0-9]+)$/',$user_step,$matches)){
    $order = $matches[1];
    $task_id = $matches[2];
    $list_id = $matches[3];

    $db->q("UPDATE tbl_tasks SET task_description = ? WHERE id = ?", [$text, $task_id]);

    $text = "🔹بسیار عالی\!\!  
ادامه گام‌های زیر رو به ترتیب برای افزودن وظیفه جدید طی می‌کنیم 👇  
~🟢 **گام 1**: افزودن نام برای وظیفه  ~
~🟢 **گام 2**: افزودن توضیحات وظیفه  ~
🟡 **گام 3**: افزودن تاریخ وظیفه  
\_\_\_  
>  🔵 **تاریخ مورد نظر خود را با فرمت زیر وارد کنید:**
>   1403\/07\/02\-14:30
    ";
    
    update_step("add_date_to_task_".$task_id."_".$list_id);
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => "MarkdownV2",
            'force_reply' => true,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🔙 لغو و بازگشت', 'callback_data' => 'view_list_' . $list_id]],
                ]
            ]
        ]);
} else if (preg_match('/^(add_date_to_task)_([0-9]+)_([0-9]+)$/',$user_step,$matches)){
    $order = $matches[1];
    $task_id = $matches[2];
    $list_id = $matches[3];

$pattern = '/^
    (1[45][0-9]{2})          # گروه 1: سال (1400 تا 1599)
    \/                       # جداکننده برای تاریخ
    (0[1-9]|1[0-2])          # گروه 2: ماه (01 تا 12)
    \/                       # جداکننده برای تاریخ
    (0[1-9]|[12][0-9]|3[01]) # گروه 3: روز (01 تا 31)
    -                        # جداکننده بین تاریخ و ساعت
    (0[0-9]|1[0-9]|2[0-3])   # گروه 4: ساعت (00 تا 23)
    :                        # جداکننده برای ساعت
    ([0-5][0-9])             # گروه 5: دقیقه (00 تا 59)
/x';

if (preg_match($pattern, $text, $matches)) {
    $year = $matches[1];
    $month = $matches[2];
    $day = $matches[3];
    $hour = $matches[4];
    $minute = $matches[5];


    $unix_time = jalaliToUnix("$year/$month/$day", "$hour:$minute");

    $db->q("UPDATE tbl_tasks SET task_date = FROM_UNIXTIME(?) WHERE id = ?", [$unix_time, $task_id]);

    $text = "🔗 وظیفه با موفقیت اضافه شد.";
    update_step(null);
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'text' => $text,
        'reply_markup' => $keyboard_start
    ]);
}
else {
    $text = "✍️ لطفا تاریخ و ساعت را با فرمت صحیح وارد کنید\.
>مثال:
>1403\/07\/02\-14:30
";
    bot("sendMessage", [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => "MarkdownV2",
        'force_reply' => true,
    ]);
}

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
} else if (($text == "🔙 بازگشت به منوی اصلی" || $text == "🔙 بازگشت")) {
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
