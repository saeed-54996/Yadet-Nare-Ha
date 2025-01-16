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



//adminm($content);

if (preg_match('/^([a-z_]+)_(\d+)$/', $cdata, $matches)) {

    $order = $matches[1];
    $list_id = $matches[2];

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
                        [['text' => 'مشاهده وظایف 10 روز آینده 📋', 'callback_data' => 'view_10_tasks_' . $list_id]],
                        [['text' => 'مشاهده وظایف 30 روز آینده 🗓', 'callback_data' => 'view_30_tasks_' . $list_id]],
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
    } 
    
    
    
    
    
    else if ($order == "view_list") {
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
    }
    
    
    
    
    
    else if ($order == "add_task") {
        update_step("add_task_to_list_" . $list_id);
        $text = "🔹بسیار عالی\!\!  
گام های زیر رو به ترتیب برای افزودن وظیفه جدید طی میکنیم 👇  
🟡 گام 1 : افزودن نام برای وظیفه  
⚪️ گام 2 : افزودن توضیحات وظیفه  
⚪️ گام 3 : افزودن تاریخ وظیفه  
___  
>🔵 نامی برای وظیفه مورد نظر خود ثبت کنید، این نام برای کاربران نمایش داده خواهد شد\.
";
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
    }
}
