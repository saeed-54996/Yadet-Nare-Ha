<?php
//===============  Telegram Basic Variables:  =============
$update_id = $update["update_id"] ?? null;

// Callback query related variables
$callback_id = $update["callback_query"]['id'] ?? null;
$cdata = $update["callback_query"]['data'] ?? null;

// Message-related variables
$message = $update["callback_query"]['message'] ?? null;
$message_id = $message['message_id'] ?? null;
$chat_id = $message['chat']['id'] ?? null;
$chat_text = $message['text'] ?? null;
$message_date = $message['date'] ?? null;

// User-related variables
$tg_id = $update["callback_query"]['from']['id'] ?? null;
$first_name = $update["callback_query"]['from']['first_name'] ?? null;
$last_name = $update["callback_query"]['from']['last_name'] ?? null;
$user_username = $update["callback_query"]['from']['username'] ?? null;




//===============  Include functions:  =============
require __DIR__ . '/../functions/jalaliToUnix.php'; // Include jalaliToUnix function
require __DIR__ . '/../functions/global.php'; // Include global functions

//===============  costume functions:  =============
function update_step($step)
{
    global $db, $tg_id, $user_step;
    $db->q("UPDATE tbl_users SET step = ? WHERE tg_id = ?", [$step, $tg_id]);
    $user_step = $step;
}


/////////////////////////////////////////////////////////////////////////

if (preg_match('/^([a-z_0-9]+)_(\d+)$/', $cdata, $matches)) {

    $order = $matches[1];
    $list_id = $matches[2];
    //adminm($cdata);
    if ($order == "view_tasks") {

        $list_tasks = $db->q("SELECT * FROM tbl_tasks WHERE list_id = ?", [$list_id]);

        if ($list_tasks[0]) {
            $list_tasks_count = count($list_tasks);

            $text = "تعداد $list_tasks_count وظیفه ناتمام برای این لیست وجود دارد.";
            bot("editMessageText", [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => $text,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => 'مشاهده 30 وظیفه آخر 📋', 'callback_data' => 'view_30_tasks_' . $list_id]],
                        //[['text' => 'مشاهده وظایف 10 روز آینده 📋', 'callback_data' => 'view_10_tasks_' . $list_id]],
                        //[['text' => 'مشاهده وظایف 30 روز آینده 🗓', 'callback_data' => 'view_30_tasks_' . $list_id]],
                    ]
                ]
            ]);
        } else {
            $text = "هیچ وظیفه ای برای این لیست وجود ندارد";
            bot("editMessageText", [
                'chat_id' => $chat_id,
                'message_id' => $message_id,
                'text' => $text,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => '🔙 بازگشت', 'callback_data' => 'view_list_' . $list_id]],
                    ]
                ]
            ]);
        }
    } else if ($order == "view_30_tasks") {
        $list_tasks = $db->q("SELECT * FROM tbl_tasks WHERE list_id = ? AND is_end = 0 AND is_deleted = 0 ORDER BY task_date ASC , id ASC LIMIT 30", [$list_id]);

        foreach ($list_tasks as $task) {
            $task_id = $task['id'];
            $task_name = $task['task_name'];
            $task_description = $task['task_description'] ?? "<blockquote>📂 بدون توضیحات</blockquote>";
            $task_date = $task['task_date'] ?? null;
            $dateTime = "<blockquote>📅 بدون تاریخ</blockquote>";
            if ($task_date) {
                $task_date = convertToJalaliWithDateTime($task_date);
                $date = $task_date['Y'] . "/" . $task_date['M'] . "/" . $task_date['D'];
                $time = $task_date['H'] . ":" . $task_date['min'];
                $dateTime = $date . " " . $time;
            }
            $text .= "🔹 وظیفه: $task_name
📄 توضیحات: 
$task_description
📆 تاریخ:
$dateTime
🔗 <a href='https://t.me/YadetNareHa_robot?start=" . encrypt("edit_task_$task_id") . "'>ویرایش</a>
----------
";
        }
        bot("sendMessage", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'parse_mode' => 'HTML'
        ]);
    } else if ($order == "view_list") {
        update_step("choosing_subscribed_list");
        $list_info = $db->q("SELECT * FROM tbl_notification_lists WHERE id = ?", [$list_id]);
        $list_name = $list_info[0]['list_name'];
        $text = "📂 لیست $list_name انتخاب شد.\n\n🔹 لطفا یکی از گزینه‌های زیر را انتخاب کنید:";
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => 'مشاهده وظایف 📋', 'callback_data' => 'view_tasks_' . $list_id], ['text' => 'افزودن وظیفه ➕', 'callback_data' => "add_task_" . $list_id]],
                    [['text' => '📦 گزینه های بیشتر', 'callback_data' => 'more_options_' . $list_id]],
                ]
            ]
        ]);
    } else if ($order == "add_task") {
        update_step("add_task_to_list_" . $list_id);
        $text = "🔹بسیار عالی\!\!  
گام‌های زیر رو به ترتیب برای افزودن وظیفه جدید طی می‌کنیم 👇  
🟡 **گام 1**: افزودن نام برای وظیفه  
⚪️ **گام 2**: افزودن توضیحات وظیفه  
⚪️ **گام 3**: افزودن تاریخ وظیفه  
\_\_\_  
> 🔵 **نامی برای وظیفه مورد نظر خود وارد کنید**، این نام برای کاربران نمایش داده خواهد شد\.
";
        bot("editMessageText", [
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
    } else if ($order == "delete_list") {
        $text = "آیا واقعا میخواهید این لیست را حذف کنید؟! 😵";
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => 'بله ✅', 'callback_data' => 'confirm_delete_list_' . $list_id],
                        ['text' => 'خیر ❌', 'callback_data' => 'view_list_' . $list_id]
                    ],
                ]
            ]
        ]);
    } else if ($order == "confirm_delete_list") {
        $db->q("UPDATE tbl_notification_lists SET is_deleted = 1 WHERE id = ?", [$list_id]);
        $text = "لیست با موفقیت حذف شد 🗑";
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🔙 بازگشت', 'callback_data' => 'view_lists']],
                ]
            ]
        ]);
    } else if ($order == "more_options") {
        $text = "📦 گزینه‌های بیشتر";
        $db_list = $db->q("SELECT * FROM tbl_notification_lists WHERE id = ? AND list_owner_id = (SELECT id FROM tbl_users WHERE tg_id = ?)", [$list_id, $tg_id]);
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => 'تغییر نام لیست ✍️', 'callback_data' => 'rename_list_' . $db_list[0]['id']], ['text' => '🗑 حذف لیست', 'callback_data' => "delete_list_" . $db_list[0]['id']]],
                    [['text' => 'تغییر دسترسی ایجاد یادآوری 📝', 'callback_data' => 'e_task_rule_' . $db_list[0]['id']]],
                    [['text' => '🔙 بازگشت', 'callback_data' => 'view_list_' . $db_list[0]['id']]]
                ]
            ]
        ]);
    } else if ($order == "rename_list") {
        update_step("rename_list_" . $list_id);
        $text = "🔹 لطفا نام جدیدی برای لیست وارد کنید:";
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'force_reply' => true,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🔙 لغو و بازگشت', 'callback_data' => 'view_list_' . $list_id]],
                ]
            ]
        ]);
    }
    else if ($order == "e_task_rule"){
        $status = $db->q("SELECT task_adding_rule FROM tbl_notification_lists WHERE id = ?", [$list_id]);
        $status = $status[0]['task_adding_rule'];
        if ($status == 0) { //owner
            $text = "📂 وضعیت دسترسی فعلی : 
فقط شما میتوانید وظیفه جدید اضافه کنید ✅";
        } else if ($status == 2) { //subs
            $text = "📂 وضعیت دسترسی فعلی : 
            همه کاربران میتوانند وظیفه جدید اضافه کنند ✅";
        }

        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => 'تغییر به همه کاربران 👥', 'callback_data' => 'all_users_e_task_rule_' . $list_id]],
                    [['text' => 'تغییر دسترسی فقط برای من 👨‍💼', 'callback_data' => 'only_me_e_task_rule_' . $list_id]],
                    [['text' => '🔙 بازگشت', 'callback_data' => 'view_list_' . $list_id]],
                ]
            ]
        ]);
    }
    else if ($order == "all_users_e_task_rule") {
        $db->q("UPDATE tbl_notification_lists SET task_adding_rule = 2 WHERE id = ?", [$list_id]);
        $text = "دسترسی ایجاد یادآوری برای همه کاربران تنظیم شد ✅";
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🔙 بازگشت', 'callback_data' => 'view_list_' . $list_id]],
                ]
            ]
        ]);
    } else if ($order == "only_me_e_task_rule") {
        $db->q("UPDATE tbl_notification_lists SET task_adding_rule = 1 WHERE id = ?", [$list_id]);
        $text = "دسترسی ایجاد یادآوری فقط برای شما تنظیم شد ✅";
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'message_id' => $message_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => '🔙 بازگشت', 'callback_data' => 'view_list_' . $list_id]],
                ]
            ]
        ]);
    }
}


else if ($cdata == "view_lists") {
    $db_user = $db->q("SELECT * FROM tbl_users WHERE tg_id = ?", [$tg_id]);
    if (isset($db_user[0])) {
        $user_db_id = $db_user[0]['id'];
    }
    $lists = $db->q("SELECT * FROM tbl_notification_lists WHERE list_owner_id = ? AND is_deleted = 0", [$user_db_id]);
    if ($lists[0]) {
        $text = "📂 لیست‌های شما:";
        foreach ($lists as $list) {
            $list_id = $list['id'];
            $list_name = $list['list_name'];
            $text .= "\n\n\n---------\n🔹 $list_name";
            $text .= "\n🔗 <a href='https://t.me/YadetNareHa_robot?start=" . encrypt("view_list_$list_id") . "'>مشاهده</a>";
            //$text .= "\n🔗 <a href='https://t.me/YadetNareHa_robot?start=" . encrypt("edit_list_$list_id") . "'>ویرایش</a>";
        }
    }
    bot("editMessageText", [
        'chat_id' => $chat_id,
        'message_id' => $message_id,
        'text' => $text,
        'parse_mode' => 'HTML',
    ]);
}