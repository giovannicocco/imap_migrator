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
    <h1>Frequently Asked Questions</h1>
    <article>
        <h4>Q. Do you keep a copy of my email?</h4>
        <p>A. No, Messages are copied directly between servers only. No email data is stored on our server.</p>
        <h4>Q. Why does POP3 only show INBOX when I have other folders?</h4>
        <p>A. The POP3 protocol only supports retrieving the INBOX. To access and retrieve other folders you need to use the IMAP protocol.</p>
        <h4>Q. Do I have to enter my password?</h4>
        <p>A. Yes, however you can change your password temporarily before you transfer your email. Then after the transfer is complete change it back.
           Note: Form data is sent securely over 256 bit Secure Socket Layer connection. Session information is stored on the server using 256bit AES
           Encryption until the tranfer completes.</p>
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