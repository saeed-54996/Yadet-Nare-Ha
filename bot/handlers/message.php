<?php

//===============  Telegram Basic Variables:  =============
$chat_id = $update["message"]['chat']['id'] ?? null;
$text = $update["message"]['text'] ?? null;
$username = $update["message"]['from']['username'] ?? null;
$first_name = $update["message"]['from']['first_name'] ?? null;
$user_id = $update["message"]['from']['id'] ?? null;
$message_id = $update["message"]['message_id'] ?? null;
//===============                             =============


if($text=="/start"){
    bot("sendMessage", array('chat_id' => $chat_id,'text' =>"Hello World! mojiiii"));
}