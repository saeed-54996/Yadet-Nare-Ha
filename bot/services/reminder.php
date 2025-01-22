<?php

require_once __DIR__ . '/../functions/jalaliToUnix.php';
require_once __DIR__ . '/../functions/global.php';
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../core.php';

$db = new Database();

$current_time = new DateTime();
$tasks = $db->q('SELECT t.*, l.is_deleted as list_deleted 
    FROM `tbl_tasks` t
    JOIN `tbl_notification_lists` l ON t.list_id = l.id
    WHERE t.task_date IS NOT NULL AND t.is_deleted = 0 AND t.is_end = 0
');

foreach ($tasks as $task) {
    // Check if the task's list is deleted
    if ($task['list_deleted'] == 1) {
        continue;
    }

    $checktime = new DateTime($task['task_date']);
    $between = $current_time->diff($checktime);

    $days = $between->format('%R%a');
    if ($days == "+1" || $days == "+3" || $days == "+7") {
        // Join the subscribers and users table to get tg_id
        $subscribers = $db->q('
            SELECT u.tg_id 
            FROM `tbl_list_subscribers` s
            JOIN `tbl_users` u ON s.user_id = u.id
            WHERE s.list_id = ?', [$task['list_id']]
        );
        foreach ($subscribers as $subscriber) {
            sendNotification($subscriber['tg_id'], $task['task_name'], $days);
        }
    }
    }
}

function sendNotification($userId, $taskName, $days) {
    $message = "🔔 یادآوری: وظیفه '$taskName' شما در $days روز دیگر است. فراموش نکنید که آن را تکمیل کنید! 😊";
    bot("sendMessage", [
        'chat_id' => $userId,
        'text' => $message,
        'parse_mode' => 'markdown',
    ]);
}