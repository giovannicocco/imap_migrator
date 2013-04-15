<?php

require_once("imap_vars.php");

?>

<!DOCTYPE HTML>
<html>
<head>
<title>IMAP Migrator: About</title>
<link rel="stylesheet" href="<?php echo $imapmig_secure_url; ?>css/reset.css" type="text/css" />
<!--[if IE]>
<link rel="stylesheet" type="text/css" href="<?php echo $imapmig_secure_url; ?>css/ie.css" />
<![endif]-->
<!--[if !IE]><!-->
<link rel="stylesheet" href="<?php echo $imapmig_secure_url; ?>css/imapmig.css" type="text/css" />
 <!--<![endif]-->
<body>
    
<?php require_once("imap_header.php"); ?>
    
<section>
<div class="body">
    <h1>About IMAP Migrator</h1>
    <article>
        <p>The project started after migrating a client's website and emails over to a new server. There were several email accounts
            with multiple gigabytes of email going back for years and I wasn't sure what the best way to transfer them was. After doing
            some research I found some PHP scripts which did the job but they required knowledge of web programming and
        were time consuming to use. I thought there should be a simpler way to transfer email between accounts so started building IMAP Migrator.</p>
        
        <h2>Features</h2>
        <h3>Recursive Folders</h3>
        <p>IMAP Migrator duplicates exactly the source's folder list on the target server.</p>
        <h3>Existing Messages</h3>
        <p>IMAP Migrator will check if a message exists before copying it to a folder.</p>
        <h3>HTML5, CSS3, jQuery</h3>
        <p>Pages and layout were designed using <a href="http://www.html5rocks.com/" target="_blank">HTML5</a> and <a href="http://css3.info" target="_blank">CSS3</a>.
            DOM manipulation and AJAX functionality built with <a href="http://jquery.com" target="_blank">jQuery</a>.</p>
        
        <h2>Challenges</h2>
        <h3>Shared Hosting</h3>
        <p>Shared web hosting often limits how long a PHP script can run. Instead of 1 instance running until everything finishes, IMAP
            Migrator uses AJAX to cycle the script until all the messages are copied.</p>
        <h3>Security</h3>
        <p>Sending account information unencrypted can be a security concern so IMAP Migrator sends data over a 256 bit Secure Socket
            Layer. Session information is stored on the server using AES 256 bit encryption and deleted after the migration completes.</p>
        
    </article>
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
    
 <p><a href="disclaimer.php">Disclaimer</a> | Copyright &copy; 2011 <a href="http://www.kentarofischer.com/">Kentaro Fischer</a>.</p>
</div> 
</footer>

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