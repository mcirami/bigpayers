<?php

	if($_SERVER["REMOTE_ADDR"] == '192.168.10.1') {
		$path = "";
	} else {
		$path = 'resources/landers/pesogrind/';
	}


?>

<!DOCTYPE html>

<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width" />
		<meta http-equiv="Cache-Control" content="no-cache" />
		<link href="resources/landers/pesogrind/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link href="resources/landers/pesogrind/css/main.css" rel="stylesheet" />
		<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
		<link rel="icon" href="resources/landers/pesogrind/favicon.ico" type="image/x-icon" />
		<script type="text/javascript" src="resources/landers/pesogrind/js/jquery-2.2.3.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="resources/landers/pesogrind/js/bootstrap/bootstrap.min.js"></script>
		<script src="resources/landers/pesogrind/js/main.js"></script>
		<title>Peso Grind</title>

	</head>
	<body>
		<div class="header full_width">
			<div class="container">
				<div class="logo float-left">
					<a href="/"><img src="resources/landers/pesogrind/images/logo.png" alt=""></a>
				</div>
				<div class="button_wrap float-right">
					<a class="button" href="http://stats.pesogrind.com/login">Login</a>
				</div>
			</div>
		</div>
		<div class="hero full_width">
			<div class="container">
				<div class="hero_copy">
					<div class="title_box full_width">
						<h2 class="text-uppercase">Grind Your</h2>
						<h2 class="text-uppercase green">Way To Cash</h2>
						<p>At PesoGrind we are here to help you earn  the most money for your efforts. Contact us to get more details and get set up today!</p>
					</div>
				</div>
			</div><!-- .container -->
		</div>
		<div class="full_width three_col_section">
			<div class="container">
				<div class="column">
					<div class="icon_wrap full_width">
						<img src="resources/landers/pesogrind/images/icon-person.png" alt="">
					</div>
					<h4 class="full_width">Name</h4>
					<p class="full_width">Christine</p>
				</div>
				<div class="column">
					<div class="icon_wrap full_width">
						<img src="resources/landers/pesogrind/images/icon-email.png" alt="">
					</div>
					<h4 class="full_width">Email</h4>
					<p class="full_width"><a href="mailto:cjangc@gmail.com">cjangc@gmail.com</a></p>
				</div>
				<div class="column">
					<div class="icon_wrap full_width">
						<img src="resources/landers/pesogrind/images/icon-skype.png" alt="">
					</div>
					<h4 class="full_width">Skype</h4>
					<p class="full_width"><a href="skype:live:admin_143865?add">christine.joyce.cacdac</a></p>
				</div>
			</div><!-- .container -->
		</div>
	</body>
</html>
