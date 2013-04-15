<?php

require_once("imap_vars.php");

/* bring in classes */
require_once("classes/AES.class.php");
/* end including classes */

/* begin misc functions */


function set_php_params() {
    global $imapmig_time_limit,
           $imapmig_error_reporting;
    
    error_reporting($imapmig_error_reporting);
    ini_set('zlib.output_compression', 'Off');
    ini_set('memory_limit','128M');
    ini_set("max_execution_time", $imapmig_time_limit);
    set_time_limit($imapmig_time_limit);
    set_error_handler("imapmig_error_handler");
}

function imapmig_error_handler($errno, $errstr, $errfile, $errline,$context)
{
  global $imapmig_errors;
    switch ($errno) {
    case E_USER_ERROR:
        t("<span style=\"color:red;\">FATAL ERROR: ".$errno ." ". $errstr ." ". $errfile ." ". $errline ." ". $context ."</span>");
        exit(1);
        break;

    case E_USER_WARNING:
        t("<span style=\"color:red;\">WARNING: ".$errno ." ". $errstr ." ". $errfile ." ". $errline ." ". $context ."</span>");
        $imapmig_errors[] = $errno ." ". $errstr ." ". $errfile ." ". $errline ." ". $context;
        break;

    case E_USER_NOTICE:
        t("<span style=\"color:red;\">NOTICE: ".$errno ." ". $errstr ." ". $errfile ." ". $errline ." ". $context ."</span>");
        $imapmig_errors[] = $errno ." ". $errstr ." ". $errfile ." ". $errline ." ". $context;
        break;

    default:
        t("<span style=\"color:red;\">Unknown Error: ".$errno ." ". $errstr ." ". $errfile ." ". $errline ." ". $context ."</span>");
        $imapmig_errors[] = $errno ." ". $errstr ." ". $errfile ." ". $errline ." ". $context;
        break;
    }

    /* Don't execute PHP internal error handler */
    return true;
}

function obj2arr($obj) {
    return json_decode(json_encode($obj), true);       
}

function t ($t = "test")
{
  global $imapmig_log_file,
          $imapmig_debug,
          $imapmig_status;
  
  if (is_object($t)) { $t = obj2arr($t); }
  
  if (is_string($t)) { $t = $t . "\n"; }
  $imapmig_status[] = $t;
  
  if (is_array($t)) {
    foreach ($t AS $v) { write_log($imapmig_log_file, $v); }
  }
  else if (!is_object($t)) { write_log($imapmig_log_file, $t); }
 
  if (!$imapmig_debug) { return false; } // if debug is not enabled, dont echo
  
  if (is_array($t)) {
    foreach ($t AS $v) { 
      if (is_array($v)) { print_r($v); } else { echo($v . "<br />"); } 
    }
  }
  else if (is_object($t)) { print_r($t); }
  else { echo($t); }
  
  echo("<br />"); 
}

function get_pointer($needle, $haystack) {
    $found = false;
    for ($i=0;$i < sizeof($haystack);$i++) {
        if (stristr($haystack[$i], $needle) != false) { $found = $i; break; }
    }
    if ($found != false) { return $found; } else { return false; }
}

function imap_error_alert($f) {
    t("function: " . $f . " error: ".imap_last_error()." ".imap_alerts());
    return imap_last_error();
}

function strip_connectionstring($connectionstring, $mailbox) {
  return str_replace($connectionstring, '', $mailbox);
}

function strip_mailbox_names($connectionstring, $mailboxes) {
  for ($i = 0; $i < sizeof($mailboxes); $i++) {
    $mailboxes[$i] = strip_connectionstring($connectionstring, $mailboxes[$i]);
  }
  return $mailboxes;
}

function utf7_encode ($mailbox) {
  return mb_convert_encoding($mailbox, "UTF7-IMAP","ISO-8859-1");
}

function im_ping ($imap, $connectionstring)
{
  global $imapmig_log_file;
  
  if (@imap_ping($imap)) { //@write_log($imapmig_log_file, $connectionstring . " ++ Ping successful, I\'m aliiive!"); 
  }
  else { @write_log($imapmig_log_file, $connectionstring . ' Ping failed, I\'m dead..' . imap_last_error()); }
}

function write_log($filename, $message)
{
  global $imapmig_write_log,
          $imapmig_timezone;
  if ($imapmig_write_log) {
    date_default_timezone_set($imapmig_timezone);  
    $data = date("d-m-Y H:i:s") . "\t" . $message . "\r\n";
    @file_put_contents($filename, $data, FILE_APPEND);
  }
}

function start_mysql_link() {
    global $link,
        $imapmig_log_file,
        $mysql_host_local,
        $mysql_host_remote,
        $mysql_login_local,
        $mysql_login_remote,
        $mysql_pass_local,
        $mysql_pass_remote,
        $mysql_db_local,
        $mysql_db_remote;
    
    
    if (isset($link) && !mysql_ping($link)) { 
        mysql_close($link);
        $link = false; 
    }
    
    if (!isset($link) || !$link) {
        if ($_SERVER['REMOTE_ADDR'] == "127.0.0.1") {
            $link = mysql_connect($mysql_host_local, $mysql_login_local, $mysql_pass_local)
            OR die("Unable to connect to MySQL Server.");
            mysql_select_db($mysql_db_local) OR @write_log($imapmig_log_file, $mysql_host . " " . mysql_error());
        }
        else {
            $link = mysql_connect($mysql_host_remote, $mysql_login_remote, $mysql_pass_remote)
            OR die("Unable to connect to MySQL Server.");
            mysql_select_db($mysql_db_remote) OR @write_log($imapmig_log_file, $mysql_host . " " . mysql_error());
        }
    }
}

function imapmig_check_time($mailbox_key, $message_number, $message_size = false) {
  global $cycle_run_time,
          $start_time,
          $reset_time,
          $large_message_size,
          $message_size_reset_time;
          
      $time_left = $cycle_run_time - (microtime(true) - $start_time);
      
      if ($time_left < $reset_time) {
        imapmig_save_session($mailbox_key, $message_number);
        close_imapmig("restart"); 
      }
      
      if ( $message_size && $message_size > $large_message_size && $time_left < $message_size_reset_time)
      {
        imapmig_save_session($mailbox_key, $message_number);
        close_imapmig("restart"); 
      }
}

function imapmig_server_encode($s) {
    global $aes,
            $aes_key, 
            $aes_mode, 
            $aes_iv;
    
    if (!isset($aes) || !$aes) { $aes = new AES($aes_key, $aes_mode, $aes_iv); }

    $s = json_encode($s);
    $s = $aes->encrypt($s);
    $s = base64_encode($s);
    
    return $s;
}

function imapmig_server_decode($s) {
    global $aes,
            $aes_key, 
            $aes_mode, 
            $aes_iv;
    
    if (!isset($aes) || !$aes) { $aes = new AES($aes_key, $aes_mode, $aes_iv); }

    $s = base64_decode($s);
    $s = $aes->decrypt($s);
    $s = json_decode($s, true);
    
    return $s;
}

function imapmig_message_options($headerinfo)
{
    $message_options = "";
    if ($headerinfo->Unseen != "U") { $message_options .= '\\Seen '; }
    if ($headerinfo->Flagged == "F") { $message_options .= '\\Flagged '; }
    if ($headerinfo->Answered == "A") { $message_options .= '\\Answered '; }
    if ($headerinfo->Deleted == "D") { $message_options .= '\\Deleted '; }
    if ($headerinfo->Draft == "X") { $message_options .= '\\Draft '; }
    $message_options = trim($message_options, " ");
    return $message_options;
}

function imapmig_get_session($sid) {
    global $imapmig_session,
            $imapmig_log_file,
            $source_cfg,
            $target_cfg;
    
    start_mysql_link();
    $query = "SELECT * FROM sessions WHERE session_id='".$sid."' AND session_ip='".$_SERVER['REMOTE_ADDR']."' LIMIT 1";
    $result = mysql_query($query) OR @write_log($imapmig_log_file, $mysql_host . " " . mysql_error());
    if (mysql_num_rows($result)) {
        $imapmig_session = mysql_fetch_array($result);
        $imapmig_session['source_server'] = imapmig_server_decode($imapmig_session['source_server']);
        $source_cfg = $imapmig_session['source_server'];
        $imapmig_session['target_server'] = imapmig_server_decode($imapmig_session['target_server']);
        $target_cfg = $imapmig_session['target_server'];
        $imapmig_session['source_mailboxes'] = json_decode($imapmig_session['source_mailboxes'], true);
        $imapmig_session['target_mailboxes'] = json_decode($imapmig_session['target_mailboxes'], true);
        $imapmig_session['current_headers'] = json_decode($imapmig_session['current_headers'], true);
        return true;
    }
    else { 
        $imapmig_session = false;
        return false;
    }
}

function imapmig_save_session($current_mailbox, $current_message, $current_headers = false, $source_mailboxes = false, $target_mailboxes = false) {
    global $mysql_host,
            $mysql_table,
            $imapmig_log_file,
            $cookie_time,
            $imapmig_session,
            $source_cfg,
            $target_cfg;
    
    if (!isset($imapmig_session['session_id']) || !$imapmig_session['session_id']) {
        $imapmig_session_id = md5(uniqid(microtime()) . $_SERVER['REMOTE_ADDR'] . $_SERVER['HTTP_USER_AGENT']);
        
        start_mysql_link();
        
        $query = "INSERT INTO ".$mysql_table." (id, session_id, session_ip, source_server, source_mailboxes, target_server, target_mailboxes, current_mailbox, ";
        $query .= "current_headers, current_message) ";
        $query .= "VALUES(0, '".$imapmig_session_id."', '";                          // session id
        $query .= $_SERVER['REMOTE_ADDR']."', ";                                    // session ip
        $query .= "'".imapmig_server_encode($source_cfg)."', ";                         // source server
        if (sizeof($source_mailboxes)) { $query .= "'".json_encode($source_mailboxes)."', "; }
        else { $query .= "'', "; }                                                      // source boxes
        $query .= "'".imapmig_server_encode($target_cfg)."', ";                          // target server
        if (sizeof($target_mailboxes)) { $query .= "'".json_encode($target_mailboxes)."', "; }
        else { $query .= "'', "; }                                                   // target boxes
        $query .= $current_mailbox.", ";                                             // current mailbox
        if ($current_headers) { $query .= "'".json_encode($current_headers)."', "; } // current headers
        else { $query .= "'', "; }
        $query .= $current_message.");";
        
        mysql_query($query) OR @write_log($imapmig_log_file, __LINE__ . " " . $mysql_host . " " . mysql_error());
        setcookie("imapmig", $imapmig_session_id, time() + $cookie_time);
        $imapmig_session['session_id'] = $imapmig_session_id;
        $imapmig_session['session_ip'] = $_SERVER['REMOTE_ADDR'];
        $imapmig_session['current_mailbox'] = $current_mailbox;
        if ($current_headers) { $imapmig_session['current_headers'] = $current_headers; }
        else { $imapmig_session['current_headers'] = ""; }
        $imapmig_session['current_message'] = $current_message;
        
        return true;
    }
    else if (imapmig_get_session($imapmig_session['session_id'])) {
        $query = "UPDATE ".$mysql_table." SET ";
        
        if ($source_mailboxes) { $query .= "source_mailboxes='".json_encode($source_mailboxes)."', "; }             // source boxes
        if ($target_mailboxes) { $query .= "target_mailboxes='".json_encode($target_mailboxes)."', "; }             // target boxes
        
        $query .= "current_mailbox=".$current_mailbox.", ";                                             // current mailbox
        if ($current_headers) { $query .= "current_headers='".json_encode($current_headers)."', "; }    // current headers
        $query .= "current_message=".$current_message." ";                                              // current message
        $query .= "WHERE id=".$imapmig_session['id']." AND session_ip='".$_SERVER['REMOTE_ADDR']."' LIMIT 1";
        mysql_query($query) OR @write_log($imapmig_log_file, $mysql_host . " " . mysql_error());
        setcookie("imapmig", $imapmig_session['session_id'], time() + $cookie_time);
        
        $imapmig_session['current_mailbox'] = $current_mailbox;
        if ($current_headers) { $imapmig_session['current_headers'] = $current_headers; }
        $imapmig_session['current_message'] = $current_message;
        return true;
    }
    else { return false; }
}

function close_imapmig($status, $values = array()) {
    global $mysql_table,
            $mysql_host,
            $imapmig_log_file,
            $imapmig_status,
            $imapmig_errors,
            $imapmig_session, 
            $source_imap, 
            $target_imap;
    
    $imapmig_status['status'] = $status;
    $imapmig_status['session_id'] = $imapmig_session['session_id']; 
    if (sizeof($imapmig_errors)) { $imapmig_status['errors'] = $imapmig_errors; }
    
    // clear session and cookie
    if ($status == "finished!" && isset($imapmig_session['session_id'])) {
        $query = "DELETE FROM ".$mysql_table." WHERE session_id='".$imapmig_session['session_id']."' AND session_ip='".$_SERVER['REMOTE_ADDR']."' LIMIT 1";
        //mysql_query($query) OR @write_log($imapmig_log_file, $mysql_host . " " . mysql_error());
        setcookie("imapmig", "", time() - 86400);
    }
    
if ($source_imap && !@imap_close($source_imap)) { @write_log($imapmig_log_file, $source_cfg['connectionstring'] . ' -- Error closing source connection'); }
if ($target_imap && !@imap_close($target_imap)) { @write_log($imapmig_log_file, $target_cfg['connectionstring'] . ' -- Error closing target connection'); }
    
exit($_GET['jsoncallback'] . "(" . json_encode($imapmig_status). ")");
}

/* end misc functions */

?>