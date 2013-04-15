<?php

/*
if (@$_SERVER['HTTPS'] != "on" && $_SERVER['REMOTE_ADDR'] != "127.0.0.1")
{
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location:".$redirect);
    exit;
}
*/

//http://localhost/imap/imap.php?jsoncallback=jQuery16403940124456501949_1317751968460&imapmig_source_service=imap&imapmig_source_host=secure575.hostgator.com&imapmig_source_port=993&imapmig_source_user=imap%40yogakula.com&imapmig_source_pass=asdfasdf&imapmig_source_ssl=on&imapmig_target_service=imap&imapmig_target_host=s37959.gridserver.com&imapmig_target_port=993&imapmig_target_user=imap%40yogakula.com&imapmig_target_pass=asdfasdf&imapmig_target_ssl=on&_=1317752036593
//http://localhost/imap/imap.php?jsoncallback=jQuery16403940124456501949_1317751968460&imapmig_source_service=imap&imapmig_source_host=imap.gmail.com&imapmig_source_port=993&imapmig_source_user=sonicade%40gmail.com&imapmig_source_pass=1nnovate&imapmig_source_ssl=on&imapmig_target_service=imap&imapmig_target_host=s37959.gridserver.com&imapmig_target_port=993&imapmig_target_user=imap%40yogakula.com&imapmig_target_pass=asdfasdf&imapmig_target_ssl=on&_=1317752036593
//http://localhost/imap/imap.php?jsoncallback=jQuery16403940124456501949_1317751968460&imapmig_source_service=imap&imapmig_source_host=imap.gmail.com&imapmig_source_port=993&imapmig_source_user=crimzion%40gmail.com&imapmig_source_pass=maibatsu&imapmig_source_ssl=on&imapmig_target_service=imap&imapmig_target_host=s37959.gridserver.com&imapmig_target_port=993&imapmig_target_user=imap%40yogakula.com&imapmig_target_pass=asdfasdf&imapmig_target_ssl=on&_=1317752036593

/* bring in functions */
require_once("imap_functions.php");
/* end including functions */

set_php_params();

date_default_timezone_set($imapmig_timezone);

/* process GET variables */

/* http://localhost/imap/imap.php?
 * imapmig_source_host=secure575.hostgator.com
 * &imapmig_source_port=993
 * &imapmig_source_service=imap
 * &imapmig_source_ssl=on
 * &imapmig_source_user=imap%40yogakula.com
 * &imapmig_source_pass=asdfasdf
 * &imapmig_source_tls=on
 * &imapmig_target_host=s37959.gridserver.com
 * &imapmig_target_port=993
 * &imapmig_target_service=imap
 * &imapmig_target_ssl=on
 * &imapmig_target_user=imap%40yogakula.com
 * &imapmig_target_pass=asdfasdf
 * &imapmig_target_tls=on
 */

if ( isset($_GET['imapmig_source_host']) && 
     isset($_GET['imapmig_source_service']) && 
     isset($_GET['imapmig_source_port']) && 
     isset($_GET['imapmig_source_user']) && 
     isset($_GET['imapmig_source_pass']) && 
     isset($_GET['imapmig_target_host']) && 
     isset($_GET['imapmig_target_service']) && 
     isset($_GET['imapmig_target_port']) && 
     isset($_GET['imapmig_target_user']) && 
     isset($_GET['imapmig_target_pass']) )
{ 
    $get_errors = array();
    
    $source_cfg = array();
    $source_host = false;
    $source_port = false;
    if (strpos($_GET['imapmig_source_host'], ":") != false) {
        $source_host = substr($_GET['imapmig_source_host'], 0, strpos($_GET['imapmig_source_host'], ":"));
        $source_port = substr($_GET['imapmig_source_host'], strpos($_GET['imapmig_source_host'], ":")+1, strlen($_GET['imapmig_source_host']));
        if (preg_match("/^([A-Za-z0-9_.-]+)$/i", $source_host)) { $source_cfg['host'] = $source_host; }
        else { $source_host = false; $get_errors[] = "invalid source host"; }
        if (preg_match("/^([0-9]+)$/i", $source_port)) { $source_cfg['port'] = $source_port; }
        else { $source_port = false; $get_errors[] = "invalid source port"; }
    }
    if (!$source_host && preg_match("/^([A-Za-z0-9_.-]+)$/i", $_GET['imapmig_source_host'])) { $source_cfg['host'] = $_GET['imapmig_source_host']; }
    else { $get_errors[] = "invalid source_host"; }
    if (!$source_port && preg_match("/^([0-9]+)$/i", $_GET['imapmig_source_port'])) { $source_cfg['port'] = $_GET['imapmig_source_port']; }
    else { $get_errors[] = "invalid source port"; }
    
    if ($_GET['imapmig_source_service'] == "imap" || $_GET['imapmig_source_service'] == "pop3") { $source_cfg['service'] = $_GET['imapmig_source_service']; }
    else { $get_errors[] = "invalid service"; }
    if (isset($_GET['imapmig_source_ssl']) && $_GET['imapmig_source_ssl'] == "on") { $source_cfg['ssl'] = "ssl"; }
    else { $source_cfg['ssl'] = ""; }
    if (isset($_GET['imapmig_source_readonly']) && $_GET['imapmig_source_readonly'] == "on") { $source_cfg['readonly'] = "readonly"; }
    else { $source_cfg['readonly'] = ""; }
    if (preg_match("/^([A-Za-z0-9_.-@]+)$/i", $_GET['imapmig_source_user'])) { $source_cfg['user'] = $_GET['imapmig_source_user']; }
    else { $source_cfg['ssl'] = ""; }
    if (strlen($_GET['imapmig_source_pass']) > 0) { $source_cfg['pass'] = $_GET['imapmig_source_pass']; }
    else { $get_errors[] = "invalid password"; }
    if (isset($_GET['imapmig_source_validatecert']) && $_GET['imapmig_source_validatecert'] == "on") { $source_cfg['validatecert'] = "validate-cert"; }
    else { $source_cfg['validatecert'] = "novalidate-cert"; }
    //else { $source_cfg['validatecert'] = ""; }
    if (isset($_GET['imapmig_source_tls']) && $_GET['imapmig_source_tls'] == "on") { $source_cfg['tls'] = "tls"; }
    //else { $source_cfg['tls'] = "notls"; }
    else { $source_cfg['tls'] = ""; }
    
    $target_cfg = array();
    $target_host = false;
    $target_port = false;
    if (strpos($_GET['imapmig_target_host'], ":") != false) {
        $target_host = substr($_GET['imapmig_target_host'], 0, strpos($_GET['imapmig_target_host'], ":"));
        $target_port = substr($_GET['imapmig_target_host'], strpos($_GET['imapmig_target_host'], ":")+1, strlen($_GET['imapmig_target_host']));
        if (preg_match("/^([A-Za-z0-9_.-]+)$/i", $target_host)) { $target_cfg['host'] = $target_host; }
        else { $target_host = false; $get_errors[] = "invalid target host"; }
        if (preg_match("/^([0-9]+)$/i", $target_port)) { $target_cfg['port'] = $target_port; }
        else { $target_port = false; $get_errors[] = "invalid target port"; }
    }
    if (!$target_host && preg_match("/^([A-Za-z0-9_.-]+)$/i", $_GET['imapmig_target_host'])) { $target_cfg['host'] = $_GET['imapmig_target_host']; }
    else { $get_errors[] = "invalid target_host"; }
    if (!$target_port && preg_match("/^([0-9]+)$/i", $_GET['imapmig_target_port'])) { $target_cfg['port'] = $_GET['imapmig_target_port']; }
    else { $get_errors[] = "invalid target port"; }
    
    if ($_GET['imapmig_target_service'] == "imap" || $_GET['imapmig_target_service'] == "pop3") { $target_cfg['service'] = $_GET['imapmig_target_service']; }
    else { $get_errors[] = "invalid service"; }
    if (isset($_GET['imapmig_target_ssl']) && $_GET['imapmig_target_ssl'] == "on") { $target_cfg['ssl'] = "ssl"; }
    else { $target_cfg['ssl'] = ""; }
    if (preg_match("/^([A-Za-z0-9_.-@]+)$/i", $_GET['imapmig_target_user'])) { $target_cfg['user'] = $_GET['imapmig_target_user']; }
    else { $target_cfg['ssl'] = ""; }
    if (strlen($_GET['imapmig_target_pass']) > 0) { $target_cfg['pass'] = $_GET['imapmig_target_pass']; }
    else { $get_errors[] = "invalid password"; }
    if (isset($_GET['imapmig_target_validatecert']) && $_GET['imapmig_target_validatecert'] == "on") { $target_cfg['validatecert'] = "validate-cert"; }
    else { $target_cfg['validatecert'] = "novalidate-cert"; }
    //else { $target_cfg['validatecert'] = ""; }
    if (isset($_GET['imapmig_target_tls']) && $_GET['imapmig_target_tls'] == "on") { $target_cfg['tls'] = "tls"; }
    //else { $target_cfg['tls'] = "notls"; }
    else { $target_cfg['tls'] = ""; }

    
    if (sizeof($get_errors)) { close_imapmig("error", $get_errors); }
    
    $source_cfg['connectionstring'] = "{";
    $source_cfg['connectionstring'] .= $source_cfg['host'];
    $source_cfg['connectionstring'] .= ":" . $source_cfg['port'] . "/";
   // $source_cfg['connectionstring'] .= "debug/";
    $source_cfg['connectionstring'] .= $source_cfg['service'] . "/";
    $source_cfg['connectionstring'] .= $source_cfg['ssl'] . "/";
    $source_cfg['connectionstring'] .= $source_cfg['validatecert'] . "/";
    $source_cfg['connectionstring'] .= $source_cfg['tls'] . "/";
    $source_cfg['connectionstring'] .= $source_cfg['readonly'] . "/";
    $source_cfg['connectionstring'] = trim($source_cfg['connectionstring'], "/");
    $source_cfg['connectionstring'] .= "}";
    $source_cfg['connectionstring'] = str_replace("//", "/", $source_cfg['connectionstring']);
    
    //if ($source_cfg['host'] == "imap.gmail.com") { $source_cfg['connectionstring'] .= "INBOX"; }
    
    $target_cfg['connectionstring'] = "{";
    $target_cfg['connectionstring'] .= $target_cfg['host'];
    $target_cfg['connectionstring'] .= ":" . $target_cfg['port'] . "/";
   // $target_cfg['connectionstring'] .= "debug/";
    $target_cfg['connectionstring'] .= $target_cfg['service'] . "/";
    $target_cfg['connectionstring'] .= $target_cfg['ssl'] . "/";
    $target_cfg['connectionstring'] .= $target_cfg['validatecert'] . "/";
    $target_cfg['connectionstring'] .= $target_cfg['tls'] . "/";
    $target_cfg['connectionstring'] = trim($target_cfg['connectionstring'], "/");
    $target_cfg['connectionstring'] .= "}";
    $target_cfg['connectionstring'] = str_replace("//", "/", $target_cfg['connectionstring']);
    
}


/* look for cookie and process session */

if ( isset($_COOKIE['imapmig']) && 
     strlen($_COOKIE['imapmig']) == 32 &&
     ctype_alnum($_COOKIE['imapmig']) )
{
    $save_source = $source_cfg;
    $save_target = $target_cfg;
    $source_mailboxes = false;
    $target_mailboxes = false;
    $imapmig_ready = false;
    
    // if there is a cookie, look for it in the database
    if (imapmig_get_session($_COOKIE['imapmig'])) {
        // if session IS found by cookie, good to go!
        $source_cfg = $save_source;
        $target_cfg = $save_target;
        $source_mailboxes = $imapmig_session['source_mailboxes'];
        $target_mailboxes = $imapmig_session['target_mailboxes'];
    }
    else {
        // if the session isnt found (or ip doesnt match), make a new session using the above $_GET data
        $imapmig_session['session_id'] = false;
        $imapmig_session['current_headers'] = "";
        $imapmig_session['source_mailboxes'] = "";
        $imapmig_session['target_mailboxes'] = "";
        imapmig_save_session(0,0); // create new session
        $imapmig_ready = true;
    }
}
else {
        // if no cookie set then make a new session using the above $_GET data
        $imapmig_session['session_id'] = false;
        $imapmig_session['current_headers'] = "";
        $imapmig_session['source_mailboxes'] = "";
        $imapmig_session['target_mailboxes'] = "";
        imapmig_save_session(0,0); // create new session
        $imapmig_ready = true;
}




/* begin imap scripts */







/* connect to source server and get mailboxes */

t("Source: Connecting to ".strtoupper($source_cfg['service'])." server: " . $source_cfg['host']);

$source_imap = imap_open($source_cfg['connectionstring'], $source_cfg['user'], $source_cfg['pass']) 
OR close_imapmig("error", array(0 => "Unable to connect: " . imap_error_alert("imap_open")));

if ($source_imap) { t("Source: ".$source_cfg['host']." Connected Successfully!"); }

if ($imapmig_session['source_mailboxes'] == "") {
    /* get list of mailboxes */
    $source_mailboxes = imap_list($source_imap, $source_cfg['connectionstring'], "*") OR t("imap_list " .imap_error_alert("imap_list"));
    $source_mailboxes = strip_mailbox_names($source_cfg['connectionstring'], $source_mailboxes);
    $imapmig_session['source_mailboxes'] = $source_mailboxes;

    t("Source Mailboxes");
    foreach ($source_mailboxes AS $mailbox) { t($mailbox); }
}


/* connect to target server and get mailboxes */

t("Target: Connecting to ".strtoupper($source_cfg['service'])."  server: " . $source_cfg['host']);

$target_imap = imap_open($target_cfg['connectionstring'], $target_cfg['user'], $target_cfg['pass']) 
OR close_imapmig("error", array(0 => "Unable to connect: " . imap_error_alert("imap_open")));

if ($target_imap) { t("Target: ".$target_cfg['user']." Connected Successfully!"); }

if ($imapmig_session['target_mailboxes'] == "") {
    /* get list of mailboxes */
    $target_mailboxes = imap_list($target_imap, $target_cfg['connectionstring'], "*");
    $target_mailboxes = strip_mailbox_names($target_cfg['connectionstring'], $target_mailboxes);
    $imapmig_session['target_mailboxes'] = $target_mailboxes;
    
    t("Target Mailboxes");
    foreach ($target_mailboxes AS $mailbox) { t($mailbox); }

}

if (!$source_mailboxes)
{
    $source_mailboxes = $imapmig_session['source_mailboxes'];
    $target_mailboxes = $imapmig_session['target_mailboxes'];
}
else { imapmig_save_session(
        $imapmig_session['current_mailbox'], 
        $imapmig_session['current_message'],
        false,
        $imapmig_session['source_mailboxes'],
        $imapmig_session['target_mailboxes']);
}

if ($imapmig_ready) { close_imapmig("ready"); }



/* begin migration */

for ($smkey=0; $smkey < sizeof($source_mailboxes); $smkey++)
{
    $mailbox = $source_mailboxes[$smkey];
    
    if (isset($imapmig_session) && ($imapmig_session['current_mailbox'] > $smkey) ) // if current mailbox is less than total mailboxes
    { 
        t("Source: Skipping Mailbox: " . $mailbox); 
        continue; 
    }
    
    $target_mailbox = imap_utf7_encode( $target_cfg['connectionstring'] . strip_connectionstring($source_cfg['connectionstring'], $mailbox) );
    $imapmig_session['current_mailbox'] = $smkey;
    
    
    switch ($mailbox)
    {
        case $mailbox:  // if $mailbox is already in $target_mailboxes array, don't create it, break out of switch
        if (array_search($mailbox, $target_mailboxes) !== false) { break; }
    
        default: // otherwise try to create the folder on the target server
        if (imap_createmailbox($target_imap, $target_mailbox) )
        {
            $imapmig_session['target_mailboxes'][] = $mailbox;
            imapmig_save_session(
              $imapmig_session['current_mailbox'], 
              $imapmig_session['current_message'],
              false,
              false,
              $imapmig_session['target_mailboxes']);
        }
        else {
            @write_log($target_cfg['connectionstring'] . " -- Error creating mailbox " . $target_mailbox . " Error: " . imap_error_alert("imap_createmailbox"));
            t("Target: Skipping Mailbox (Cant Create): " . $smkey);
            continue 2; // abort & skip migrating this mailbox
        }
    }
    
    // if pop3, don't try to reopen source connection
    if ($source_cfg['service'] != "pop3") {
        /* reopen source imap for current mailbox to begin copying */
        imap_reopen($source_imap, $source_cfg['connectionstring'] . $mailbox)   
        OR close_imapmig("error", array(0 => "Unable to connect: " . imap_error_alert("imap_reopen")));
    }
    
    
    /* get number of messages in source mailbox */
    $source_info = imap_check($source_imap) OR imap_error_alert("imap_check");

    /* reopen target imap to new mailbox */
    imap_reopen($target_imap, $target_cfg['connectionstring'] . $mailbox)   
    OR close_imapmig("error", array(0 => "Unable to connect: " . imap_error_alert("imap_reopen")));
        
    // if session storing current mailbox and all messages transferred, update session
    if ( ($imapmig_session['current_mailbox'] == $smkey) &&
         ($imapmig_session['current_message'] == $source_info->Nmsgs) )
    {
        $imapmig_session['current_message'] = 0;
        t("Target: Skipping Mailbox ".$mailbox." (All Messages Transferred)");
        continue;
    }
    
    t("Source: Preparing ".$mailbox);
    
    
    // get total messages in target mailbox
    //t("Mailbox: ".$mailbox);
    $target_info = imap_check($target_imap) OR imap_error_alert("imap_check");
    
    
    // if headers are not loaded, fetch overview from target
    if ($imapmig_session['current_headers'] == "")
    {
        if ($target_info->Nmsgs > 0)
        {
        $target_headers = imap_fetch_overview($target_imap, "1:{$target_info->Nmsgs}", 0);
        $target_headers = obj2arr($target_headers);
        $target_message_ids = array();
        for ($i=0;$i<sizeof($target_headers);$i++) {
            if (!isset($target_headers[$i]['message_id']) || strlen($target_headers[$i]['message_id']) == 0) {
            $target_headers[$i]['message_id'] = md5($target_headers[$i]['subject'].
              $target_headers[$i]['from'].
              $target_headers[$i]['to'].
              $target_headers[$i]['date']); 
            }
            $target_message_ids[$i] = $target_headers[$i]['message_id']; 
        }
        imapmig_save_session($smkey, $imapmig_session['current_message'], $target_message_ids);
        }
        else if ($target_info->Nmsgs == 0)
        {
        // set the headers to a value so they don't recache
        //$imapmig_session['current_headers'] = array(0 => array(0 => "cached")); t("Target mailbox empty, no headers to cache."); 
        imapmig_save_session($smkey, $imapmig_session['current_message'], array(0 => array(0 => "cached")));
        }
    }
  
    
  /* begin transfering messages */
  
  if ($source_info->Nmsgs > 0) 
  { 
    $source_messages = imap_fetch_overview($source_imap, "1:{$source_info->Nmsgs}", 0) OR imap_error_alert("imap_fetch_overview");
    
    for ($i=0; $i < $source_info->Nmsgs; $i++)
    {
      if (isset($imapmig_session) && ($imapmig_session['current_message'] > $i) ) // if current message is less than total messages
      { $i = $imapmig_session['current_message']; }
      
      $overview = $source_messages[$i];
      
      if (!isset($overview->date) || 
          strlen($overview->date) > 38) {
          $invalid_message = "<span style=\"color:red\">Skipping message #".$i.": Invalid header.";
          if (isset($overview->date)) { $invalid_message .= " Invalid Date format: ".$overview->date; }
          if (isset($overview->subject)) { $invalid_message .= " Subject: ".$overview->subject; }
          t($invalid_message."</span>");          
          continue;
      }

      if ($source_cfg['service'] == "pop3" || !isset($overview->message_id) || strlen($overview->message_id) == 0) {
          $source_message_id = md5($overview->subject.
          $overview->from.
          $overview->to.
          $overview->date); 
      }
      else { $source_message_id = $overview->message_id; }

      if (array_search($source_message_id, $imapmig_session['current_headers']) !== false) {
          $dupe_found = "Skipping existing message #".$i." [".$overview->date."] ";
          if (isset($overview->subject)) { $dupe_found .= htmlspecialchars($overview->subject); }          
          t($dupe_found);           
          imapmig_check_time($smkey, $i, false);
          continue; 
      }

      imapmig_check_time($smkey, $i, $overview->size);
      
      $source_headerinfo = imap_headerinfo($source_imap, $overview->msgno) OR imap_error_alert("imap_headerinfo");
      
      $source_header = imap_fetchheader($source_imap, $overview->msgno) OR imap_error_alert("imap_fetchheader");      
      $source_body = imap_body($source_imap, $overview->msgno) OR imap_error_alert("imap_body");
      $message = $source_header . $source_body;
      $message_options = imapmig_message_options($source_headerinfo);
      
      //Tue, 30 Aug 2011 13:30:43 -0700
      //RFC 2822
      //internaldate needs rfc2060
      $message_date = date("d-M-Y H:i:s O", strtotime($overview->date));
      
      imap_append($target_imap, $target_mailbox, $message, $message_options, $message_date) 
      OR @write_log($target_cfg['connectionstring'] . " -- Error migrating message " . $overview->msgno . ": " . $source_header . imap_error_alert("imap_append") . PHP_EOL);

      $copy_success = "Source: Copied message #" . $overview->msgno . " ";             
      if (isset($overview->subject)) { $copy_success .= $overview->subject." "; }
      $copy_success .= "to Target: " . $target_cfg['host']." ( ".$overview->size." bytes )";
      t($copy_success);

      
      /* preserve flags on original messages */
      if ($source_cfg['readonly'] != "readonly" && strstr($message_options, '\\Seen') == false) {
        imap_clearflag_full($source_imap, $overview->msgno, '\\Seen') OR imap_error_alert("imap_clearflag_full");
      }
      
      imapmig_check_time($smkey, $i, false);      
      
      $imapmig_session['current_message'] = $i;
    } // message copies for loop finished
    
      imapmig_check_time($smkey, $i, false);
      
  }
  else { @write_log($target_cfg['connectionstring'] . " -- skipping mailbox " . $mailbox . PHP_EOL); }
  
  // finished mailbox, reset current message to 0
  $imapmig_session['current_message'] = 0;
  $imapmig_session['current_headers'] = "";
  
}

$end_time = microtime(true);

//t("Script run time: " . ($end_time - $start_time));

close_imapmig("finished");

?>