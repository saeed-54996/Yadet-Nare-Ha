<?php

require_once __DIR__ . '/../functions/jalaliToUnix.php';
require_once __DIR__ . '/../../db.php';

$db = new Database();

//date_default_timezone_set('Asia/Tehran');


$current_time = new DateTime();
$sub = $db->q('SELECT * FROM `tbl_tasks` WHERE task_date IS NOT NULL AND is_deleted = 0');
foreach ($sub as $s){
  $checktime = new DateTime($s['task_date']);
  $between = $current_time->diff($checktime);
  if($between->format('%R%a') == "+0"){
    // do somthing
  }
  //echo $s['task_name'] . " == " . $between->format('%R%a days, %H hours');
  //echo "<br>";
};
      

