<?php

/* bring in functions */
require_once("imap_functions.php");
/* end including functions */

if ( isset($_COOKIE['imapmig']) && 
     strlen($_COOKIE['imapmig']) == 32 &&
     ctype_alnum($_COOKIE['imapmig']) )
{
  if (imapmig_get_session($_COOKIE['imapmig'])) {
        $query = "DELETE FROM ".$mysql_table." WHERE session_id='".$imapmig_session['session_id']."' AND session_ip='".$_SERVER['REMOTE_ADDR']."'";
        mysql_query($query) OR @write_log('imap_migrator.txt', $mysql_host . " " . mysql_error());
        setcookie("imapmig", "", time() - 86400);
        exit("success");
  }
  else { exit("failed"); }  
}
  else { exit("failed"); }  

?>