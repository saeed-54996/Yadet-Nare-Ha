<?php

$config = require __DIR__ . '/config.php';
define('BOT_TOKEN', $config['bot_token']);
define('ADMIN_ID', $config['admin_id']);
define('ENCRYPTION_KEY', $config['encryption_key']);
define('API_URL', 'https://api.telegram.org/bot' . BOT_TOKEN . '/');

function bot($method, $parameters = []) {
    if (!$parameters) {
        $parameters = [];
    }
    $parameters["method"] = $method;
    
    $handle = curl_init(API_URL);
    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($handle, CURLOPT_TIMEOUT, 60);
    curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
    curl_setopt($handle, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);

    $result = curl_exec($handle);
    $res = json_decode($result, true);

    if (!$res['ok']) {
        bot("sendMessage", ['chat_id' => ADMIN_ID, 'text' => json_encode($res)]);
    }
    return $res;
}
