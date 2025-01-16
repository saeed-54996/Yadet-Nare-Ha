<?php


function adminm($text){
    //send message to admin:
    bot('SendMessage', ['chat_id' => ADMIN_ID, 'text' => $text]);
}