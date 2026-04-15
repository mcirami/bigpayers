<!DOCTYPE html>
<html lang="en">
	<head>
    <title><?=$company->shortHand?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="/login_themes/<?=$company->login_theme?>/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="/login_themes/<?=$company->login_theme?>/css/style.css">
		<link rel="stylesheet" media="screen" type="text/css"
		      href="<?php echo $webroot; ?>css/default.css"/>
		<link rel="stylesheet" media="screen" type="text/css"
		      href="<?php echo $webroot; ?>css/company.php"/>
		<script type="text/javascript" src="/login_themes/<?=$company->login_theme?>/js/jquery-3.3.1.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="/login_themes/<?=$company->login_theme?>/js/bootstrap/bootstrap.min.js"></script>
	</head>
	<body>

		<div class="row h-100">
			<div class="col-12 col-md-6 text-center d-flex align-content-center left_column">
			</div>
			<div class="col-12 col-md-6 d-block value_span1 right_column">
				<div class="row m-auto">
					<div class="col-7 col-lg-6 mx-auto mb-4 p-3">
						<img class="logo mb-3" src="<?= \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/logo.png" alt="">
						<p></p>
					</div>
				</div>
				<div class="row my-0 mx-auto m-md-auto w-100">
					<div class="col-md-10 col-lg-8 mx-auto login_box text-left">
						<h3 class="mb-2">Sign In</h3>
						<form action="" method="post">
							<div class="form-group login">
								<input name="txt_uname_email" type="text" class="form-control" id="txt_uname_email" placeholder="Username or e-mail address">
							</div>
							<div class="form-group password mb-0">
								<input type="password" class="form-control" id="txt_password" name="txt_password" placeholder="Password">
							</div>
							<a href="aff_help.php">Forgot Password?</a>
							<button type="submit" class="btn btn-primary d-block text-center mt-2 value_span6-2 value_span2 value_span1-2">Sign In</button>
						</form>
					</div>
				</div>
			</div>
		</div>

	</body>
</html>
