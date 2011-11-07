<?php
ini_set("max_execution_time", "7200");
ob_implicit_flush(1);

require('config.php');
require('functions.php');
  
$sQueryUpdate = "UPDATE `Gallery` SET `gallery_status` = 'approved' WHERE `gallery_status` = 'cron' LIMIT 1;";  
startup();
$ResultSelect = mysql_query($sQueryUpdate) or die(mysql_error());
mysql_close(); 

?>
