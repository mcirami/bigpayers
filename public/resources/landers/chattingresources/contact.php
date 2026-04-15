<?php
if($_SERVER["REMOTE_ADDR"] == '192.168.10.1') {
	$path = "";
} else {
	$path = '/resources/landers/chattingresources/';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'lib/phpmailer/src/Exception.php';
require 'lib/phpmailer/src/PHPMailer.php';
require 'lib/phpmailer/src/SMTP.php';


date_default_timezone_set('America/New_York');

$ip = $_SERVER['REMOTE_ADDR'];

$nameErr = $emailErr = $messageErr = "";
$name = $email = $message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

	$post = true;

	/*if(isset($_GET['name'])) {
		$emailTemplate = $_GET['name'];
	} else {
		$emailTemplate = '';
	}*/

	if (empty($_POST["name"])) {
		$nameErr = "Name is required";
		$post = false;
	} else {
		$name = test_input($_POST["name"]);

		if (!preg_match("/^[a-zA-Z ]*$/",$name)) {
			$nameErr = "Only letters and white space allowed";
			$post = false;
		}
	}

	if (empty($_POST["email"])) {
		$emailErr = "email is required";
		$post = false;
	} else {
		$email = test_input($_POST["email"]);

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$emailErr = "Invalid email format";
			$post = false;
		}
	}

	if (empty($_POST["message"])) {
		$messageErr = "Message is required";
		$post = false;
	} else {
		$message = test_input($_POST["title"]);

		if (!preg_match("/^[a-zA-Z ]*$/",$message)) {
			$titleErr = "Only letters and white space allowed";
			$post = false;
		}
	}


	if ($post != false) {

		//echo "Success! Row ID: {$mysqli->insert_id}";
		$_POST['submit'] = "success";

		$mail = new PHPMailer(true);

		try {
			$name = $_POST["name"]; // HINT: use preg_replace() to filter the data
			$email = $_POST["email"];
			$message = $_POST["message"];

			//$mail->SMTPDebug = 2;
			$mail->IsSMTP(); // set mailer to use SMTP
			$mail->Host = "smtp.gmail.com";  // specify main and backup server
			$mail->SMTPAuth = true;     // turn on SMTP authentication
			$mail->Username = "admin@chattingresources.com";  // SMTP username
			$mail->Password = "J7#us73@20s"; // SMTP password
			$mail->SMTPSecure = "tls";
			$mail->Port = 587;
			$mail->setFrom($email);

			if($_SERVER['REMOTE_ADDR'] !== '192.168.10.1') {
				//$mail->AddAddress("jeff@moneylovers.com");
				$mail->AddAddress("admin@chattingresources.com");
			}else {
				$mail->AddAddress("matteo@mscwebservices.net");
			}
			//$mail->AddAddress("ellen@example.com");                  // name is optional
			$mail->AddReplyTo($email, $name);

			$mail->WordWrap = 50;                                 // set word wrap to 50 characters
			//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
			//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
			$mail->IsHTML(true);                                  // set email format to HTML

			$mail->Subject = "Chatting Resources Contact Form";

			$emailContent = "Name:<br>" . $name .
				"<br><br>Email:<br> " . $email .
				"<br><br>Message:<br> " . $message;

			$mail->Body = $emailContent;
			$mail->AltBody = $emailContent;

			/*if (!$mail->Send()) {
				echo "Message could not be sent. <p>";
				//echo "Mailer Error: " . $mail->ErrorInfo;
			} else {
				echo "success";
			}*/

			$mail->send();
			//echo 'Message has been sent';

		} catch (Exception $e) {
			echo 'Message could not be sent.';
			echo 'Mailer Error: ' . $mail->ErrorInfo;
		}
	}
}


function test_input($data) {
	$data = trim($data);
	$data = stripslashes($data);
	$data = htmlspecialchars($data);
	return $data;
}

?>


<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		<link href="<?php echo $path; ?>css/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link href="<?php echo $path; ?>css/main.css" rel="stylesheet" />
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
		<link rel="icon" href="<?php echo $path; ?>favicon.ico" type="image/x-icon" />
		<script type="text/javascript" src="<?php echo $path; ?>js/jquery-2.2.3.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="<?php echo $path; ?>js/bootstrap/bootstrap.min.js"></script>
		<script src="<?php echo $path; ?>js/main.js"></script>
		<link href="https://fonts.googleapis.com/css?family=Montserrat:700" rel="stylesheet">
		<title>Chatting Resources</title>

	</head>
	<body>
		<div class="header full_width">
			<div class="container">

				<nav class="navbar navbar-expand-md">
					<a class="navbar-brand logo" href="/"><img src="<?php echo $path; ?>images/logo.png" alt=""></a>
					<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01" aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
								<span class="navbar-toggler-icon mobile_menu_icon">
									<span></span>
									<span></span>
									<span></span>
								</span>
					</button>
					<div class="collapse navbar-collapse" id="navbarTogglerDemo01">
						<ul class="navbar-nav ml-auto mt-auto mb-auto mt-lg-0">
							<li class="nav-item">
								<a class="nav-link" href="http://chattingresources.com/signup.php">Signup</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="https://training.chattingresources.com/member-access20035741?page_id=20035740&page_key=9n0t3t9ubgq2sw79&page_hash=c3bbeb71605&login_redirect=1">Training</a>
							</li>
							<li class="nav-item">
								<a class="nav-link" href="/?section=contact">Contact us</a>
							</li>
							<li class="nav-item">
								<div class="button_wrap">
									<a class="button" href="http://stats.chattingresources.com/login">Login</a>
								</div>
							</li>
						</ul>
					</div>
				</nav>
			</div>
		</div>
		<div class="contact full_width">
			<div class="container">
				<div class="page_title text-center">
					<h2>Contact Us</h2>
					<p>If you have any questions or issues, please feel free to contact us. We will be more than happy to help you in any way that we can, and we look forward to speaking with you.</p>
				</div>
				<div class="full_width form_wrap mt-5">

					<?php if (!isset($_POST['submit'])) : ?>

						<form method="post" action="<?php htmlspecialchars($_SERVER['PHP_SELF']); ?>">
							<div class="form-group">
								<label for="name">Name</label>
								<input name="name" type="text" class="form-control" id="name" value="<?php if (isset($_POST['name'])) { echo $_POST['name']; } ?>">
								<div class="errors">
									<p class="text-danger"><?php echo $nameErr; ?></p>
								</div>
							</div>
							<div class="form-group">
								<label for="email">Email Address</label>
								<input name="email" type="email" class="form-control" id="email" value="<?php if (isset($_POST['email'])) { echo $_POST['email']; } ?>">
								<div class="errors">
									<p class="text-danger"><?php echo $emailErr; ?></p>
								</div>
							</div>
							<div class="form-group">
								<label for="message">Message</label>
								<textarea name="message" id="message" rows="10" class="form-control text_area"><?php if (isset($_POST['message'])) { echo $_POST['message']; } ?></textarea>
								<div class="errors">
									<p class="text-danger"><?php echo $messageErr; ?></p>
								</div>
							</div>
							<button type="submit" class="button float-left">Submit</button>
						</form>

					<?php else : ?>

						<div class="text-center success_message">
							<h2 class="text-uppercase">Thanks for Your Inquiry!</h2>
							<p>We will be contacting you soon with a response!</p>
						</div>

					<?php endif; ?>

				</div>

			</div>
		</div>
		<div class="full_width footer">
			<div class="container">
				<div class="full_width text-center">
					<img src="<?php echo $path; ?>images/people-icon-small.png" alt="">
				</div>
				<div class="full_width">
					<ul class="list-inline">
						<li class="list-inline-item">
							<a href="http://chattingresources.com/signup.php">Signup</a>
						</li>
						<li class="list-inline-item">
							<a href="https://training.chattingresources.com/member-access20035741?page_id=20035740&page_key=9n0t3t9ubgq2sw79&page_hash=c3bbeb71605&login_redirect=1">Training</a>
						</li>
						<li class="list-inline-item">
							<a href="/?section=contact">Contact Us</a>
						</li>
						<li class="list-inline-item">
							<a href="http://stats.chattingresources.com/login">Login</a>
						</li>
					</ul>
				</div>
				<div class="full_width">
					<p>Copyright 2018 chattingresources.com. All rights reserved.</p>
				</div>
			</div>
		</div>
	</body>
</html>
