<?php

//$db->q("SELECT * FROM users WHERE username = ? AND age > ?", [$username, $age]);

$db->q("INSERT INTO tbl_users (username, tg_name, tg_id) VALUES (?, ?, ?)", [$username,$first_name.$last_name, $user_id]);