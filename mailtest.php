<?php

error_reporting("E_ALL");

function strip_cs($cs, $mailbox) {
  return str_replace($cs, '', $mailbox);
}

function t ($t = "test<br />") { echo $t; }

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

function obj2arr($obj) {
    return json_decode(json_encode($obj), true);       
}

function get_pointer($needle, $haystack) {
    $found = false;
    for ($i=0;sizeof($haystack);$i++) {
        if (stristr($haystack[$i], $needle) != false) { $found = $i; break; }
    }
    if ($found != false) { return $found; } else { return false; }
}



//$source_connect = "{gator575.hostgator.com:110/pop3/novalidate-cert}";
$source_connect = "{pop.mail.yahoo.com:110/pop3/novalidate-cert}";
//$source_connect = "{secure575.hostgator.com:993/imap/ssl/novalidate-cert}";
//$source_user = "imap@yogakula.com";
//$source_pass = "asdfasdf";
$source_user = "bf96743";
$source_pass = "lovers898";

$target_connect = "{s37959.gridserver.com:993/imap/ssl/novalidate-cert}";
//$target_connect = "{secure575.hostgator.com:993/imap/ssl/novalidate-cert}";
$target_user = "imap@yogakula.com";
$target_pass = "asdfasdf";

$source_imap = imap_open($source_connect, $source_user, $source_pass);
$target_imap = imap_open($target_connect, $target_user, $target_pass);

if (!$source_imap) { echo "source: could not connect.<br />"; }
else { echo "source: successfully connected.<br />"; }
if (!$target_imap) { echo "target: could not connect.<br />"; }
else { echo "target: successfully connected.<br />"; }

$source_mailboxes = imap_list($source_imap, $source_connect, "*");
$target_mailboxes = imap_list($target_imap, $target_connect, "*");

for ($i=0; $i<sizeof($source_mailboxes); $i++) {
  $source_mailboxes[$i] = strip_cs($source_connect, $source_mailboxes[$i]);
  echo "Source mailbox: ";
  echo $source_mailboxes[$i]. "<br />";
}
for ($i=0; $i<sizeof($target_mailboxes); $i++) {
  $target_mailboxes[$i] = strip_cs($target_connect, $target_mailboxes[$i]);
  echo "target mailbox: ";
  echo $target_mailboxes[$i]. "<br />";
}

foreach ($source_mailboxes as $mb) {

  //echo "Reopening source to: ".$mb."<br />";
  //imap_reopen($source_imap, $source_connect . $mb) or die(imap_last_error());
  
  $target_mailbox = imap_utf7_encode( $target_connect . $mb );

  if (array_search($mb, $target_mailboxes) === false) {
    if (!imap_createmailbox($target_imap, $target_mailbox)) { continue; }
  }
  
  echo "Reopening target to: ".$mb."<br />";
  imap_reopen($target_imap, $target_connect . $mb);
  
  $source_info = imap_check($source_imap);
  
  $source_messages = imap_fetch_overview($source_imap, "1:{$source_info->Nmsgs}", 0) or die(imap_last_error());
  
  t($source_messages);
  
  for ($i=0; $i < $source_info->Nmsgs; $i++) {
    $overview = $source_messages[$i];
    t("Message id: ". $overview->message_id. "<br />");
    $t = obj2arr($overview);
    foreach ($t as $tt) { echo $tt."<br>"; }
    $source_headerinfo = imap_headerinfo($source_imap, $overview->msgno) or die(imap_last_error());
    $source_header = imap_fetchheader($source_imap, $overview->msgno) or die(imap_last_error());
    $source_body = imap_body($source_imap, $overview->msgno) or die(imap_last_error());
    $message = $source_header . $source_body;
    $message_options = imapmig_message_options($source_headerinfo) or die(imap_last_error());

    echo "Copying message #". $i . "<br />";
    //imap_append($target_imap, $target_mailbox, $message, $message_options) or die(imap_last_error());
  }       
}

close_imap($source_imap);
close_imap($target_imap);

?>