<?php

//$db->q("SELECT * FROM users WHERE username = ? AND age > ?", [$username, $age]);

//$db->q("INSERT INTO tbl_users (username, tg_name, tg_id) VALUES (?, ?, ?)", [$username,$first_name.$last_name, $user_id]);

$res = $db->q("SELECT * FROM tbl_users WHERE id = 55");
bot("sendMessage", ['chat_id' => ADMIN_ID, 'text' => json_encode($res)]);