<?php

if (@$_SERVER['HTTPS'] != "on" && $_SERVER['REMOTE_ADDR'] != "127.0.0.1")
{
    $redirect = "https://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
    header("Location:".$redirect);
    exit;
}

require_once("imap_functions.php");

/* look for session cookie */

if ( isset($_COOKIE['imapmig']) && 
     strlen($_COOKIE['imapmig']) == 32 &&
     ctype_alnum($_COOKIE['imapmig']) )
{
    // if cookie exists, check for it in the database
    if (imapmig_get_session($_COOKIE['imapmig'])) {

        $imapmig_form_pop_data = array(
            "imapmig_source_host" => $source_cfg['host'],
            "imapmig_source_port" => $source_cfg['port'],
            "imapmig_source_service" => $source_cfg['service'],
            "imapmig_source_ssl" => (isset($source_cfg['ssl']))?1:0,
            "imapmig_source_readonly" => (isset($source_cfg['readonly']))?1:0,
            "imapmig_source_user" => $source_cfg['user'],
            "imapmig_source_pass" => $source_cfg['pass'],
            "imapmig_source_validatecert" => (isset($source_cfg['validatecert']))?1:0,
            "imapmig_source_tls" => (isset($source_cfg['tls']))?1:0,
            "imapmig_target_host" => $target_cfg['host'],
            "imapmig_target_port" => $target_cfg['port'],
            "imapmig_target_service" => $target_cfg['service'],
            "imapmig_target_ssl" => (isset($target_cfg['ssl']))?1:0,
            "imapmig_target_readonly" => (isset($target_cfg['readonly']))?1:0,
            "imapmig_target_user" => $target_cfg['user'],
            "imapmig_target_pass" => $target_cfg['pass'],
            "imapmig_target_validatecert" => ($target_cfg['validatecert'])?1:0,
            "imapmig_target_tls" => (isset($target_cfg['tls']))?1:0
        );
        $imapmig_form_pop_data = json_encode($imapmig_form_pop_data);
        
    }
}



?>

<!DOCTYPE HTML>
<html>
<head>
<title>IMAP Migrator</title>
<link rel="stylesheet" href="<?php echo $imapmig_secure_url; ?>css/reset.css" type="text/css" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="<?php echo $imapmig_secure_url; ?>css/ie.css" />
<![endif]-->
<!--[if !IE]><!-->
<link rel="stylesheet" href="<?php echo $imapmig_secure_url; ?>css/imapmig.css" type="text/css" />
 <!--<![endif]-->
<script type="text/javascript" src="<?php echo $imapmig_secure_url; ?>js/imapmig_functions.js"></script>
<script type="text/javascript" src="<?php echo $imapmig_secure_url; ?>js/jquery-1.6.4.min.js"></script>
<script type="text/javascript" src="<?php echo $imapmig_secure_url; ?>js/jquery.cookie-modified.js"></script>
<script type="text/javascript" src="<?php echo $imapmig_secure_url; ?>js/jquery.populate.pack.js"></script>
<script type="text/javascript">
<!--

    var imapmig_status = false;
    var getjson_loop = true;
    var getjson_resume = false;
    var imapmig_cycles = 0;
    var status_window_pos = "bottom";
    
    function move_status_up() {
      $('#imapmig_status').fadeTo(1000, 1.0 );
      $('#imapmig_status').animate( {"margin-top": "-=432px"}, { duration: 500, queue: false});
      $('#imapmig_status').animate( {"height": "+=200px"}, { duration: 500, queue: false});
      status_window_pos = "top";
      $('#button_start').prop('disabled', true);
      $('#button_clear').prop('disabled', false);
      $('#button_start').addClass('disabled');
      $('#button_clear').removeClass('disabled');
    }
    function move_status_down() {
      $('#imapmig_status').fadeTo(1000, 0.5 );
      $('#imapmig_status').animate( {"margin-top": "+=432px"}, { duration: 500, queue: false});
      $('#imapmig_status').animate( {"height": "-=200px"}, { duration: 500, queue: false});
      status_window_pos = "bottom";
      $('#button_start').prop('disabled', false);
      $('#button_clear').prop('disabled', true);
      $('#button_start').removeClass('disabled');
      $('#button_clear').addClass('disabled');
    }
    
    
    function run_imapmig()
    {
        imapmig_cycles++;
        imapmig_errors = new Array();
        $.getJSON('<?php echo $imapmig_secure_url; ?>imap.php?jsoncallback=?',
                $('#imapmig_form').serialize(),
                function(data) { /* start function(data) */
                    for (d in data) {
                        if (data[d] == data['status']) { continue; }
                        if (data[d] == data['session_id']) { storage.imapmig_session_id = data['session_id']; continue; }
                        if (data[d] == "errors") {
                          for (e in data[d]) { imapmig_errors.push(data[d][e]); }
                          continue;
                        } 
                        $('#imapmig_status').append("<p>"+data[d]+"</p>");
                    }
                    switch (data.status) {
                       case "ready": $('#imapmig_status').append("<p>Starting Migration...</p>"); break;
                       case "restart": $('#imapmig_status').append("<p><br />Continuing Migration...</p>"); break;
                       case "finished": 
                          $('#imapmig_status').append("<p>Mail Migration Complete!</p>");
                          storage.removeItem("imapmig_session_id");
                          break;
                       case "error": $('#imapmig_status').append("<p>Error:</p>");
                    }
                if (imapmig_errors.length > 0) {
                  $('#imapmig_status').append("<p><br /><strong>ERRORS</strong></p>");
                  for (e in imapmig_errors) { $('#imapmig_status').append("<p>"+imapmig_errors[e]+"</p>"); }
                }
                if ($('#imapmig_status').not(":focus")) { $('#imapmig_status').animate({ scrollTop: $('#imapmig_status').prop('scrollHeight') }, 5000); }
                if (data.status == "ready" || data.status == "restart") { run_imapmig(); }
        }); /* end function(data) */
    }

  function startit()
  {
    var validall = LiveValidation.massValidate([valid1, valid2, valid3, valid4, valid5, valid6, valid7, valid8]);
    if (validall) {
        if (status_window_pos == "bottom") {
            clearsess();
            imapmig_cycles = 0;
            run_imapmig();
            $('#source_config').fadeTo(1000, 0.1 );
            $('#target_config').fadeTo(1000, 0.1 );
            $('#forward_icon').fadeTo(1000, 0.1 );
            move_status_up();
        }
    }
  }
  
  function clearsess() {
    $.ajax({
    async: false,
    type: "get",
    cache: false,
    url: "imap_remsess.php",
    success: function (data) {
        if (data == "success") { del_cookie('imapmig'); }
        return(data);
    }
    });
  }
  
  function clearit() {
    cs = clearsess();
    if (cs == "success") {
        alert("Successfully deleted sessions."); 
    } 
    else { alert("No sessions found."); } 
  }
  
  
  
  function clearstat() 
  {
      if (status_window_pos == "top") {
          $('#source_config').fadeTo(1000, 1.0 );
          $('#target_config').fadeTo(1000, 1.0 );
          $('#forward_icon').fadeTo(1000, 1.0 );
          move_status_down();
          $('#imapmig_status').html("<p>Ready.</p>");
      }
  }
  
  function savelog(){
    var d = new Date;
    if (parseInt(d.getTimezoneOffset()) > 0) {
        var ts = Math.round( (d.getTime() / 1000) - (d.getTimezoneOffset() * 60) );
    }
    else { 
        var ts = Math.round( (d.getTime() / 1000) + (d.getTimezoneOffset() * 60) ); 
    }
    
    $('#imapmig_status_log').val($('#imapmig_status').text());
    $('#imapmig_status_localtime').val(ts);
    $('#imapmig_status_form').submit();
  }
  
  
 $(document).ready(function(){
    $("#imapmig_form").submit(function() { return false; });
    $('#imapmig_status').fadeTo(0, 0.5 );
    
    valid1 = new LiveValidation( 'imapmig_source_host', {validMessage: " "} );
    valid1.add( Validate.Format, { pattern: /^([A-Za-z0-9_.:-]+)$/i, failureMessage: "Invalid Hostname or IP" } );
    valid2 = new LiveValidation( 'imapmig_source_port', {validMessage: " "} );
    valid2.add( Validate.Numericality, { onlyInteger: true } );
    valid3 = new LiveValidation( 'imapmig_source_user', {validMessage: " "} );
    valid3.add( Validate.Format, { pattern: /^([A-Za-z0-9_.-@]+)$/i, failureMessage: "Invalid Username" } );
    valid4 = new LiveValidation( 'imapmig_source_pass', {validMessage: " "} );
    valid4.add( Validate.Length, { maximum: 64 } );

    valid5 = new LiveValidation( 'imapmig_target_host', {validMessage: " "} );
    valid5.add( Validate.Format, { pattern: /^([A-Za-z0-9_.:-]+)$/i, failureMessage: "Invalid Hostname or IP" } );
    valid6 = new LiveValidation( 'imapmig_target_port', {validMessage: " "} );
    valid6.add( Validate.Numericality, { onlyInteger: true } );
    valid7 = new LiveValidation( 'imapmig_target_user', {validMessage: " "} );
    valid7.add( Validate.Format, { pattern: /^([A-Za-z0-9_.-@]+)$/i, failureMessage: "Invalid Username" } );
    valid8 = new LiveValidation( 'imapmig_target_pass', {validMessage: " "} );
    valid8.add( Validate.Length, { maximum: 64 } );
    
    <?php if (isset($imapmig_form_data)) { echo "$('#imapmig_form').populate(". $imapmig_form_data.");"; } ?>
    
    storage = "";
    try {
      if (localStorage.getItem) {
        storage = localStorage;
      }
      else { storage = false; }
    } catch(e) {}

});
  
// -->
</script>

<body>
    
<?php require_once("imap_header.php"); ?>

<form name="imapmig_form" id="imapmig_form">
    
<section>
<div id="server_container" class="body">
  <article>
  <div id="source_config">
  <fieldset>
  <legend id="source_server">Source Server</legend>
  <ol>
  <li>
  <label>Service</label>
  <input type="radio" value="imap" name="imapmig_source_service" id="imapmig_source_service" checked="checked" />
  <label for="imapmig_source_service">IMAP</label>
  <input type="radio" value="pop3" name="imapmig_source_service" id="imapmig_source_service" />
  <label for="imapmig_source_service">POP3</label>
  </li>
  <li>
  <label for="imapmig_source_host" class="long-label">Mail Server Hostname</label>
  <input type="text" name="imapmig_source_host" id="imapmig_source_host" pattern="([A-Za-z0-9_.:-]+)" 
          <?php //if ($imapmig_default_values) { echo 'value="imap.gmail.com"'; } ?>
          <?php if ($imapmig_default_values) { echo 'value="secure575.hostgator.com"'; } ?>
          <?php //if ($imapmig_default_values) { echo 'value="s37959.gridserver.com"'; } ?>
          <?php //if ($imapmig_default_values) { echo 'value="pop.mail.yahoo.com"'; } ?>
         placeholder="Source Hostname or IP" required="required"/>
  </li>
  <li>
  <label for="imapmig_source_port">Port</label>
  <select name="imapmig_source_port" id="imapmig_source_port">
    <option value="995">995 (POP3 SSL)</option>
    <option value="993" selected="selected">993 (IMAP4 SSL)</option>
    <option value="585">585 (IMAP4 SSL)</option>
    <option value="110">110 (POP3)</option>
  </select>
  </li>
  <li>
  <label for="imapmig_source_user">Username</label>
  <input type="text" name="imapmig_source_user" id="imapmig_source_user" 
         <?php //if ($imapmig_default_values) { echo 'value="sonicade@gmail.com"'; } ?>
         <?php if ($imapmig_default_values) { echo 'value="imap@yogakula.com"'; } ?>
         <?php //if ($imapmig_default_values) { echo 'value="promotion@yogakula.com"'; } ?>
         <?php //if ($imapmig_default_values) { echo 'value="bf96743"'; } ?>
         placeholder="Source username" required="required"  style="clear:right;" />
  </li>
  <li>
  <label for="imapmig_source_pass">Password</label>
  <input type="password" name="imapmig_source_pass" id="imapmig_source_pass" 
         <?php //if ($imapmig_default_values) { echo 'value="1nnovate"'; } ?>
         <?php if ($imapmig_default_values) { echo 'value="asdfasdf"'; } ?>
         <?php //if ($imapmig_default_values) { echo 'value="saraswati108"'; } ?>
         <?php //if ($imapmig_default_values) { echo 'value="lovers898"'; } ?>
         placeholder="Source password" required="required"/>
  </li>
  <li>
  <input type="checkbox" name="imapmig_source_ssl" id="imapmig_source_ssl" checked="checked" />
  <label for="imapmig_source_ssl">SSL</label>
  <input type="checkbox" name="imapmig_source_tls" id="imapmig_source_tls" />
  <label for="imapmig_source_tls">TLS</label>
  <input type="checkbox" name="imapmig_source_validatecert" id="imapmig_source_validatecert" />
  <label for="imapmig_source_validatecert">Validate Certificate</label>
  </li>
  </ol>
  </fieldset>
  </div>
  </article>
    
  <aside><div id="forward_icon"><img src="images/mail-forward96.png" /></div></aside>

  <article>
  <div id="target_config">
  <fieldset>
  <legend id="target_server">Target Server</legend>
  <ol>
  <li>
  <label>Service</label>
  <input type="radio" value="imap" name="imapmig_target_service" id="imapmig_target_service" checked="checked" />
  <label for="imapmig_target_service">IMAP</label>
  <input type="radio" value="pop3" name="imapmig_target_service" id="imapmig_target_service">
  <label for="imapmig_target_service">POP3</label>
  </li>
  <li>
  <label for="imapmig_target_host" class="long-label">Mail Server Hostname</label>
  <input type="text" name="imapmig_target_host" id="imapmig_target_host" pattern="([A-Za-z0-9_.:-]+)" 
         <?php //if ($imapmig_default_values) { echo 'value="imap.gmail.com"'; } ?>
         <?php if ($imapmig_default_values) { echo 'value="s37959.gridserver.com"'; } ?>
         <?php //if ($imapmig_default_values) { echo 'value="secure575.hostgator.com"'; } ?>
         placeholder="Target Hostname or IP" required="required"/>
  </li>
  <li>
  <label for="imapmig_target_port">Port</label>
  <select name="imapmig_target_port" id="imapmig_target_port">
    <option value="995">995 (POP3 SSL)</option>
    <option value="993" selected="selected">993 (IMAP4 SSL)</option>
    <option value="585">585 (IMAP4 SSL)</option>
    <option value="110">110 (POP3)</option>
  </select>
  </li>
  <li>
  <label for="imapmig_target_user">Username</label>
  <input type="text" name="imapmig_target_user" id="imapmig_target_user" 
         <?php //if ($imapmig_default_values) { echo 'value="kfwebdev@gmail.com"'; } ?>
         <?php if ($imapmig_default_values) { echo 'value="imap@yogakula.com"'; } ?>
         <?php //if ($imapmig_default_values) { echo 'value="promotion@yogakula.com"'; } ?>
         placeholder="Target username" required="required"  style="clear:right;" />
  </li>
  <li>
  <label for="imapmig_target_pass">Password</label>
  <input type="password" name="imapmig_target_pass" id="imapmig_target_pass" 
         <?php //if ($imapmig_default_values) { echo 'value="c0ntracts"'; } ?>
         <?php if ($imapmig_default_values) { echo 'value="asdfasdf"'; } ?>
         <?php //if ($imapmig_default_values) { echo 'value="saraswati108"'; } ?>
         placeholder="Target password" required="required"/>
  </li>
  <li>
  <input type="checkbox" name="imapmig_target_ssl" id="imapmig_target_ssl" checked="checked" />
  <label for="imapmig_target_ssl">SSL</label>
  <input type="checkbox" name="imapmig_target_tls" id="imapmig_target_tls" />
  <label for="imapmig_target_tls">TLS</label>
  <input type="checkbox" name="imapmig_target_validatecert" id="imapmig_target_validatecert" />
  <label for="imapmig_target_validatecert">Validate Certificate</label>
  </li>
  </ol>
  </fieldset>
  </div>
  </article>
</div>  
</section>
    
<section>
<div id="buttons" class="body">
    <a id="button_start" href="" onclick="startit(); return false" class="button email">Start Transfer</a>
    <a id="button_clear" href="" onclick="clearstat(); return false" class="button delete disabled">Clear Status</a>
</div>
</section>

</form>

    
<section>
  <div class="body">
    <div id="imapmig_status"><p>Ready.</p></div>
    <form id="imapmig_status_form" method="post" action="<?php echo $imapmig_secure_url; ?>imap_log.php" target="_blank">
        <input type="hidden" id="imapmig_status_log" name="imapmig_status_log" />
        <input type="hidden" id="imapmig_status_localtime" name="imapmig_status_localtime" />
    </form>
  </div>
</section>

<footer>
<div id="footer" class="body">
    
 <aside>
  <div id="badges">
    <a href="http://www.comodo.com/e-commerce/ssl-certificates/secure-server.php" target="_blank"><img class="badge" src="<?php echo $imapmig_secure_url; ?>images/tl_transp.gif" /></a>
    <a href="http://en.wikipedia.org/wiki/Advanced_Encryption_Standard" target="_blank"><img class="badge" src="<?php echo $imapmig_secure_url; ?>images/aes256.png" /></a>
  </div>
 </aside>
    
 <nav>
 <div id="footer_nav">
<?php /* <a id="button_clear_sess" href="" onclick="clearit(); return false" class="button delete">Clear Sessions</a> */ ?>
<a id="button_save_log" href="" onclick="savelog(); return false" class="button save">Save Log</a>
     <!--
  <p><a href="/credits.html">Credits</a> -
     <a href="/tos.html">Terms of Service</a> -
     <a href="/index.html">Index</a></p>
     -->
  </div>
 </nav>
    
 <p><a href="disclaimer.php">Disclaimer</a> | Copyright &copy; 2011 <a href="http://www.kentarofischer.com/">Kentaro Fischer</a>.</p>

</div> 
</footer>
    
<?php /* <script type="text/javascript" src="<?php echo $imapmig_secure_url; ?>js/modernizr-2.0.6.js"></script> */ ?>
<script type="text/javascript" src="<?php echo $imapmig_secure_url; ?>js/livevalidation_standalone.compressed.js"></script>
<?php /* <script type="text/javascript" src="<?php echo $imapmig_secure_url; ?>js/curveycorners.js"></script> */ ?>

<script type="text/javascript">

  var _gaq = _gaq || [];
  _gaq.push(['_setAccount', 'UA-12716196-6']);
  _gaq.push(['_trackPageview']);

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();

</script>

</body>

</html>