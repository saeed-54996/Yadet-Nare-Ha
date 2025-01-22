<?php

require __DIR__ . '/db.php';
require __DIR__ . '/bot/core.php';

$db = new Database();

//===============   BOT MAIN =============
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!isset($update["callback_query"])) {
    require __DIR__ . '/bot/handlers/message.php';
    exit();
} else {
    require __DIR__ . '/bot/handlers/callback_query.php';
    exit();
}
