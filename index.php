<?php

require 'db.php';
require 'bot/core.php';


$db = new Database();


//===============   BOT MAIN =============
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!isset($update["callback_query"])) {
    require 'bot/handlers/message.php';
    exit();
}
else {
    require 'bot/handlers/callback_query.php';
    exit();
}