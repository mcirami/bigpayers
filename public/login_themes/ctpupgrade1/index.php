<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="/login_themes/<?=$company->login_theme?>/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="/login_themes/<?=$company->login_theme?>/css/style.css?v=1.2">
		<script type="text/javascript" src="/login_themes/<?=$company->login_theme?>/js/jquery-3.3.1.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="/login_themes/<?=$company->login_theme?>/js/bootstrap/bootstrap.min.js"></script>
        <title> <?= $company->shortHand?> </title>
	</head>
	<body>
		<div class="row h-100">
			<div class="col-12 page_wrapper d-flex align-content-center justify-content-center">
				<div class="row w-100">
					<div class="col-12 col-lg-6 left_column d-flex flex-column align-content-center justify-content-center">
						<div class="white_box">
							<h3>Seeking Models Of All Types</h3>
							<p class="mb-3">Looking to make easy extra, totally passive income?</p>
							<h4>We Offer</h4>
							<ul class="mt-2">
								<li>
									<p>Simple Extra Revenue Stream</p>
								</li>
								<li>
									<p>Automatic URL Optimization</p>
								</li>
								<li>
									<p>Set It and Forget It</p>
								</li>
								<li>
									<p>Earn More CASH!</p>
								</li>
							</ul>
							<h5 class="mb-2">Want to earn more</h5>
							<p>Recruit other Models for lifetime referral commissions!</p>
						</div>
					</div>
					<div class="col-12 col-lg-6 right_column d-flex flex-column align-content-center justify-content-center">
						<div class="logo_wrap">
							<img src="<?= \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/logo.png" alt="">
						</div>
						<div class="form_wrap">
							<h3 class="my-3">Login to your account</h3>
							<form method="post">
								<div class="form-group">
								<?php
								if (isset($error))
								{
									?>
									<div class = "alert alert-danger" style = " padding-bottom:5px;">
										<i class = "glyphicon glyphicon-warning-sign"></i> &nbsp;<span
												style = "color:red;"><?php echo $error; ?></span>
									</div>
									<?php
								}
								?>
									<label for="login">Username or e-mail address</label>
									<input placeholder="Username or e-mail address" name="txt_uname_email" type="text" class="form-control" id="txt_uname_email" value="<?php echo $user->autoFillEmail; ?>">
								</div>
								<div class="form-group">
									<label for="password">Password</label>
									<input placeholder="Password" type="password" class="form-control" id="password" name="txt_password">
								</div>
								<a href="aff_help.php">Forgot Password?</a>
								<button type="submit" class="d-block text-center w-100">Login Now</button>
							</form>
							<div class="text-center mt-3">
								<p>Don't have an account? <a class="signup_link" href="signup.php">Create One</a></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

	</body>
</html>
