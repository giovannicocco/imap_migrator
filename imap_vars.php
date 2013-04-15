<?php

$start_time = microtime(true);


/* initialize variables */
$imapmig_debug = false;

$imapmig_time_limit = 60 * 60 * 1;   // 1 hour
$imapmig_ready = false;
$imapmig_status = array();
$imapmig_error_reporting = 0;
$imapmig_error_reporting = -1; // show all errors
$imapmig_errors = array();
if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") { $imapmig_default_values = true; }
else {  $imapmig_default_values = false; }
//$imapmig_default_values = true;
//$imapmig_write_log = true;
$imapmig_log_file = "imapmig.log";
$imapmig_timezone = "America/Chicago"; // Hostgator servers in Texas, CST/CDT

$imapmig_secure_url = "https://www.kentarofischer.com/imap/";
if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") { $imapmig_secure_url = "http://localhost/imap/"; }

$cycle_run_time = 60;                // run time for each load cycle in seconds
$reset_time = 10;                    // time left before resetting the script
$message_size_reset_time = 10;       // time left before restarting script for large messages
$large_message_size = 100000;        // bytes size of message to qualify as large message
$cookie_time = 60 * 60 * 24 * 7;     // 7 days

$aes_key = "b0bab*babubb3lt3a4everpe0ples1*1";
$aes_iv = "ja5minm1lkte4111";
$aes_mode = "ECB";

$mysql_host_local = "localhost";
$mysql_host_remote = "localhost";
$mysql_login_local = "imapmig";
$mysql_login_remote = "sadmin_imapmig";
$mysql_pass_local = "b0bab0ba";
$mysql_pass_remote = "b0bab0ba";
$mysql_db_local = "imapmig";
$mysql_db_remote = "sadmin_imapmig";
$mysql_table = "sessions";

/*
$source_cfg = array();
$source_cfg['connectionstring'] = "{secure575.hostgator.com:993/imap/ssl/novalidate-cert}";
$source_cfg['user'] = "imap@yogakula.com";
$source_cfg['pass'] = "asdfasdf";

$target_cfg = array();
$target_cfg['connectionstring'] = "{s37959.gridserver.com:993/imap/ssl/novalidate-cert}";
$target_cfg['user'] = "imap@yogakula.com";
$target_cfg['pass'] = "asdfasdf";
*/

?>