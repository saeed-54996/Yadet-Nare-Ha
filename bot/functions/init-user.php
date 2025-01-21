<?php
//===============  User Initialization:  =============

//check if user exists in db
$db_user = $db->q("SELECT * FROM tbl_users WHERE tg_id = ?", [$tg_id]);

//if user exists, update username and tg_name
if (isset($db_user[0])) {
    $db->q("UPDATE tbl_users SET username = ?, tg_name = ? WHERE tg_id = ?", [$username, $first_name . $last_name, $tg_id]);
    $user_step = $db_user[0]['step'];
    $user_db_id = $db_user[0]['id']
} else { //if user does not exist, insert user into db
    $db->q("INSERT INTO tbl_users (username, tg_name, tg_id) VALUES (?, ?, ?)", [$username, $first_name . $last_name, $tg_id]);
}


//function to update user step in db:
function update_step($step)
{
    global $db, $tg_id, $user_step;
    $db->q("UPDATE tbl_users SET step = ? WHERE tg_id = ?", [$step, $tg_id]);
    $user_step = $step;
}
