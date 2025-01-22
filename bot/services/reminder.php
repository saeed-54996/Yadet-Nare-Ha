<?php

require_once __DIR__ . '/../functions/jalaliToUnix.php';
require_once __DIR__ . '/../../db.php';


$sub = $db->q('SELECT * FROM tbl_tasks WHERE user_id = ? AND list_id = ?',[$user_db_id,$list_id]);

      

