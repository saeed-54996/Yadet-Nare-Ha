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
$user_id = $update["callback_query"]['from']['id'] ?? null;
$first_name = $update["callback_query"]['from']['first_name'] ?? null;
$last_name = $update["callback_query"]['from']['last_name'] ?? null;
$user_username = $update["callback_query"]['from']['username'] ?? null;




//===============  Include functions:  =============
require __DIR__ . '/../functions/jalaliToUnix.php'; // Include jalaliToUnix function
require __DIR__ . '/../functions/global.php'; // Include global functions




adminm($content);

if (preg_match('/^([a-z_]+)_(\d+)$/', $cdata, $matches)) {

    $order = $matches[1];
    $list_id = $matches[2];

    if ($order == "view_tasks") {
        
        $list_id = $order;
        $list_tasks = $db->q("SELECT * FROM tbl_tasks WHERE list_id = ?", [$list_id]);
        
        if($list_tasks[0]){
        $list_tasks_count = count($list_tasks);
        
        $text = "تعداد $list_tasks_count وظیفه ناتمام برای این لیست وجود دارد.";
        bot("editMessageText", [
            'chat_id' => $chat_id,
            'text' => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    [['text' => 'مشاهده وظایف 10 روز آینده 📋', 'callback_data' => 'view_10_tasks_' . $list_id]],
                    [['text' => 'مشاهده وظایف 30 روز آینده 🗓', 'callback_data' => 'view_30_tasks_' . $list_id]],
                ]
            ]
        ]);
        }
        else{
            $text = "هیچ وظیفه ای برای این لیست وجود ندارد";
            bot("editMessageText", [
                'chat_id' => $chat_id,
                'text' => $text,
                'reply_markup' => [
                    'inline_keyboard' => [
                        [['text' => 'بازگشت به لیست ها', 'callback_data' => 'view_list_'.$list_id]],
                    ]
                ]
            ]);
        }
    }



}
