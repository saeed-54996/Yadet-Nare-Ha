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

    if($order == "view_tasks"){
        
    }



}
