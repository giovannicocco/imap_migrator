<?php

if (!is_numeric($_POST['imapmig_status_localtime'])) { exit; }

date_default_timezone_set("America/Chicago");  
$filename = "imapmig_log_".date("m-d-Y_h-i-s-a", $_POST['imapmig_status_localtime']).".txt;";


header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Mon, 26 Jul 1970 05:00:00 GMT"); // Date in the past
header('Content-Type: text/plain');
header("Content-Disposition: attachment; filename=".$filename);

echo "IMAP Migrator Log\r\n";
echo "Date: ".date("m-d-Y", $_POST['imapmig_status_localtime'])."\r\n";
echo "Time: ".date("h:i:sa", $_POST['imapmig_status_localtime'])."\r\n\r\n";

echo htmlspecialchars($_POST['imapmig_status_log']);

exit;

?>