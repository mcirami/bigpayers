<?php


if (isset($_GET["repid"]) && isset($_GET["offerid"]))
{
	include("landingpage.php");
	die();
}

if (isset($_GET["uid"]))
{
	include("postback.php");
	die();
}

// if its an offer url, and there wasnt any parameters for posting or generating clicks..
if (\LeadMax\TrackYourStats\System\Company::loadFromSession()->isCompanyOfferUrl($_SERVER["HTTP_HOST"]) == true)
{
	include("404.php");
	die();
}



if (\LeadMax\TrackYourStats\System\Company::getSub() != "trackyourstats")
{
	
	$company = \LeadMax\TrackYourStats\System\Company::loadFromSession();

	if ($_SERVER["HTTP_HOST"] !== $company->landing_page && $_SERVER["HTTP_HOST"] !== $company->login_url)
	{
		// test server
		if (\LeadMax\TrackYourStats\System\Company::getCustomSub() == "debug")
		{
			send_to('login');
		}
		
	}
	$lander = new \LeadMax\TrackYourStats\System\Lander($company);
	$lander->loadCompanyLander();
//    header("Location: login.php");
}

?>


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns = "http://www.w3.org/1999/xhtml">
<head>
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8"/>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	<link href = "css/style.css" rel = "stylesheet" type = "text/css"/>
	<link href = "css/arrow.css" rel = "stylesheet" type = "text/css"/>
	
	<link rel = "shortcut icon" type = "image/ico" href = "images/trackyourstats/favicon.ico"/>
	<script src = "js/prefixfree.min.js"></script>
	<script src = "js/jquery_2.1.3_jquery.min.js"></script>
	
	<title><?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?></title>
</head>
<body>
<!--header-->
<div class = "main_bg">
	<div class = "top_sec">
		<div class = "holder">
			<div class = "logo"><a href = "#"><img src = "images/logo.png" alt = "<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>" title = "<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>"/></a></div>
			<div class = "btn_login"><a href = "/login">Log In</a></div>
			<ul>
				<li><a href = "#">Pricing</a></li>
				<li><a href = "#">Contacts</a></li>
				<li><a href = "#">Features</a></li>
			</ul>
		</div>
	</div>
	<div class = "header_sec">
		<h2><span>Track your stats</span> anywhere online </h2>
		<p><em>SAVE BIG MONEY WITH TRACK YOUR STATS!</em><br> Get the tracking you need without the features you don't.<br> Get everything at your fingertips any time with real time stats!</p>
		<div class = "searchbox">
			<input type = "text"/>
			<a href = "#">Start Free Trial</a></div>
		<a class = "arrow-wrap arrow bounce" href = "#content">
			<span class = "arrow"><img src = "images/icon_arrow.png" alt = ""/></span>
			<!--<span class="hint">scroll</span>-->
		</a>
	</div>
</div>
<!--header-->
<!--section2-->
<div class = "section2" id = "content">
	<div class = "holder">
		<div class = "arrow_01"><img src = "images/arrow_02.png" alt = ""/></div>
		<h2>One platform. Real Time Stats. </h2>
		<p>A fully customizable tracking solution designed to manage all of your digital marketing efforts.</p>
		
		<div class = "sub"><img src = "images/icon01.png" alt = ""/>
			<h2>Customizable Platform</h2>
			<p>Create various account types and offers as you need them any time you want.</p></div>
		
		<div class = "sub"><img src = "images/icon02.png" alt = ""/>
			<h2>Unparalleled Support</h2>
			<p>24/7 support staff assures you are always set up and running correctly.</p></div>
		
		<div class = "sub"><img src = "images/icon03.png" alt = ""/>
			<h2>Fast and Easy</h2>
			<p>You can be setup and running offers within minutes. Start your network today!</p></div>
	</div>
</div>
<!--section2-->
<!--section3-->
<div class = "section3">
	<div class = "holder">
		<div class = "lft_col">
			<h2>How Does it Work?</h2>
			<p>Track Your Stats is designed to be simple. Take all of the guess work out of your tracking software. Get the features you need, without the bloat you don't. Create managers, affiliates, set permissions, create offers, set
			   postbacks and you are ready to start moving and shaking in the affiliate marketplace with your own network!
			</p>
			
			<p>We make this decision an easy one for you. If you ever have any questions do not hesitate to reach out. We look forward to being your trusted source for all of your marketing needs.</p>
			
			<p class = "btn"><a href = "#">Learn more</a></p>
		
		</div>
		<div class = "rt_col"><img src = "images/img_1.jpg" alt = ""/></div>
	
	</div>

</div>
<!--section3-->
<!--section4-->
<div class = "section4">
	<div class = "holder">
		<span class = "arrow_03"><img src = "images/arrow_03.png" alt = ""/></span>
		<h2>Try <span>TrackYourStats</span> Today!</h2>
		<p>Get the tracking you need without the features you don't.</p>
		<p class = "btn"><a href = "#">Get Started for Free</a></p>
	</div>
</div>
<!--section4-->


<span class = "footer_txt">&copy; 2017 TrackYourStats, Inc</span>
<script src = 'http://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js'></script>

<script src = "js/index.js"></script>
</body>
</html>
