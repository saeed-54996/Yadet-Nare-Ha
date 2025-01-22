<?php

require_once __DIR__ . '/../functions/jalaliToUnix.php';
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
    adminm("days: $days");
    if ($days == "+1" || $days == "+3" || $days == "+7") {
        // Send notification to users in the subscription list
        $subscribers = $db->q('SELECT * FROM `tbl_list_subscribers` WHERE list_id = ?', [$task['list_id']]);
        foreach ($subscribers as $subscriber) {
            // Send notification to $subscriber['user_id']
            // You can use your preferred method to send notifications (e.g., email, SMS, etc.)
            sendNotification($subscriber['user_id'], $task['task_name'], $days);
        }
    }
}

function sendNotification($userId, $taskName, $days) {
    // Implement your notification logic here
    // For example, you can send an email or SMS to the user
    echo "Notification sent to user $userId for task '$taskName' which is due in $days days.\n";
}
echo "hi this is test";