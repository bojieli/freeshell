<?php
session_start();
if (!empty($_SESSION['email']))
    die("<script>window.location.href='login.php';</script>");
session_write_close();

include_once "db.php";
include_once "distributions.inc.php";
$shellnum = mysql_result(checked_mysql_query("SELECT COUNT(*) FROM shellinfo"), 0);
?>
<!docTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>USTC freeshell</title>

<link rel="stylesheet" href="css/style.css" />
<link rel="shortcut icon" href="favicon.ico" type="image/vnd.microsoft.icon">

<!--[if lt IE 8]> <div style=' clear: both; height: 59px; padding:0 0 0 15px; position: relative;'> <a href="http://windows.microsoft.com/en-US/internet-explorer/products/ie/home?ocid=ie6_countdown_bannercode"><img src="http://storage.ie6countdown.com/assets/100/images/banners/warning_bar_0027_Simplified Chinese.jpg" border="0" height="42" width="820" alt="You are using an outdated browser. For a faster, safer browsing experience, upgrade for free today." /></a></div> <![endif]-->
<!--[if lt IE 8]>
<link rel="stylesheet" href="css/ie7_fix.css" />
<![endif]-->
<!--[if lt IE 9]>
<link rel="stylesheet" href="css/ie8_fix.css" />
<![endif]-->
<!--[if lt IE 7]>
<script src="js/jquery.pngFix.pack.js" type="text/javascript"></script>
<script type="text/javascript">
 DD_belatedPNG.fix('#loginconfirm,img');
</script>
<link rel="stylesheet" href="css/ie6_fix.css" />
<![endif]-->
<script src="js/jquery.js" type="text/javascript"></script>
<script src="js/arouse.js" type="text/javascript"></script>
<script src="js/easing.js" type="text/javascript"></script>
<script src="js/misc.js" type="text/javascript"></script>
<script src="js/register.js" type="text/javascript"></script>

</head>

<body>
	<div id="wrapper">
    <div id="header">
	    <ul>
        	<li class="headcont 1 titlehighlighted">Free</li>
            <li class="headcont 2">Powerful</li>
            <li class="headcont 3">Safe</li>
            <li class="headcont 4"><a href="faq.html">FAQ</a></li>
	    </ul>
        <div id="loginbutton">
        	<div id="logininput" style="display:none">
	        	<p>
                	<form id="loginform" action="login.php" method="post">
		            <input tabindex="1" name="email" type="text" value="Email" onclick='$(this).attr("value","")';/>
		            <input tabindex="2" name="pass" type="password"  onkeydown="subCheck();"/>
                    <input type="submit" style="display:none"/>
                    </form>
                </p>
            </div>
            <div id="loginconfirm">
        		<p id="logintext" tabindex="3">Login</p>
            </div>
        </div>
    </div>
	<div id="welcome">
	    <div class="slideshow">
		    <ul class="slide">
		    	<li class="slide1">
                <div class="slidewords">
	                  <h1>This is freeshell.</h1>
	                  <h2>Create your own Linux box!</h2>
	                  <p>You can create a free Linux box on real servers and take full control of it.</p>
                      <p>The word "free" is both in terms of free-of-change and freedom-of-use.</p>
                      <p>USTC freeshell is free, powerful and safe.</p>
                      <p class="lastline"><img src="img/tour.png"  style="vertical-align:-4px;"/></a> <a>Take the tour </a></p>
                  </div>
                </li>
		        <li class="slide2">
                <div class="slidewords">
	                  <h1>This is SCGY cluster.</h1>
	                  <h2>16G Mem, 8 Cores, 15000rpm disk * 7 nodes</h2>
                      <p>SCGY cluster, previously used for high-performance computing, is now open to public.</p>
                      <p>Unlike most VPS, each box on freeshell can make use of full capacity of the physical machine, i.e. except for disk space, there is nearly no limit.</p>
                      <p class="lastline"><img src="img/tour.png"  style="vertical-align:-4px;"/></a> <a>Take the tour </a></p>
                  </div>
                </li>
		        <li class="slide3">
                <div class="slidewords">
	                  <h1>This is OpenVZ.</h1>
	                  <h2>OpenVZ Virtualization, solid as stone.</h2>
                      <p>Based on Linux Containers, OpenVZ provides strong isolation among virtual machines with only 1% to 2% performance loss.</p>
                      <p>With OpenVZ, there can be hundreds of live Linux boxes on a single machine.</p>
                      <p class="lastline"><img src="img/tour.png"  style="vertical-align:-4px;"/></a> <a>Take the tour </a></p>
                  </div>
                </li>
		    </ul>
	    </div>
        
    </div>
    <div id="servstate">
    	<div id="servtext">
    		<p>There are <span class="number"><?php echo $shellnum; ?></span> shells. Want to join us?</p>
        </div>
        <div id="regbutton">
        	<p>Register now!</p>
        </div>
    </div>
    <div id="regplace">
    	<div id="regtitle">
        	<h1>Registration</h1>
        	<div id="progbar">
            </div>
        </div>
        <form id="regform" action="register.php" method="post">
         	<p class="descr">Before registering, you should read and agreed to the <a href="faq.html">General Policy</a>.</p>
            <p><span>E-Mail: </span><input type="text" name="regemail" id="regemail" onchange="verify('email',$(this).val())"/><span class="regcheck" id="emailfail"></span></p>
            <p><span>Password:</span><input type="password" name="regpassword" id="regpassword" onchange="if(passcheck){checkpass($(this).val(),'password')}"/><span class="regcheck"></span></p>
            <p><span>Confirm:</span><input type="password" name="regconfpass" id="regconfpass" onchange="checkpass($(this).val())"/><span class="regcheck" id="passfail"></span></p>
            <p><span>Hostname:</span><input type="text" name="hostname" id="hostname" onchange="verify('host',$(this).val());" /><span class="regcheck" id="hostfail"></span></p>
            <p><span>Hardware Node:</span><select name="nodeno" id="nodeno">
                <option selected="selected">Default</option>
                <option>1</option>
                <option>2</option>
                <option>3</option>
                <option>4</option>
                <option>5</option>
                <option>6</option>
                <option>7</option>
                </select>
            </p>
            <p><span>Distribution:</span><select name="distribution" id="distribution" onclick="distribution_click()">
                <?php echo distribution_option_html(); ?>
                <option value="gallery">Create from Gallery (NEW)</option>
                </select>
                <span class="regcheck" id="galleryfail"></span></p>
            </p>
            
            <div id="gallery" style="display:none"></div>
            <div id="regsend">
        		<p>Register!</p>
        	</div>
        </form>
    </div>
</div>
<script type="text/javascript">
$(function(){
  var _gaq = window._gaq || [];
  _gaq.push(['_setAccount', 'UA-36692506-1']);
  _gaq.push(['_setDomainName', 'blog.ustc.edu.cn']);
  _gaq.push(['_trackPageview']);
  window._gaq = _gaq;

  (function() {
    var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
  })();
});
</script>
</body>
</html>
