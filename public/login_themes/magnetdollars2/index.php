<!DOCTYPE html>
<html lang="en">
	<head>
    <title><?=$company->shortHand?></title>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link href="/login_themes/<?=$company->login_theme?>/css/bootstrap/bootstrap.min.css" rel="stylesheet" />
		<link rel="stylesheet" href="/login_themes/<?=$company->login_theme?>/css/style.css">
		<script type="text/javascript" src="/login_themes/<?=$company->login_theme?>/js/jquery-3.3.1.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="/login_themes/<?=$company->login_theme?>/js/bootstrap/bootstrap.min.js"></script>
	</head>
	<body>

		<div class="row h-100">
			<div class="col-12 col-md-6 text-center d-block d-md-flex align-content-center left_column">
				<div class="row m-auto">
					<div class="col-12 mx-auto mb-4 p-3">
                    <img class="logo mb-3" src="<?= \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/logo.png" alt="">
						  <p></p>
						<ul class="text-left mt-5">
						</ul>  
					</div>
				</div>
			</div>
			<div class="col-12 col-md-6 p-3 p-md-0 d-block d-md-flex d-flex align-content-center">
				<div class="row m-auto w-100">
					<div class="col-md-10 col-lg-8 mx-auto login_box text-left">
						<h3 class="mb-4">Login</h3>
						<form action="" method="post">
							<div class="form-group login">
								<input name="txt_uname_email" type="text" class="form-control" id="txt_uname_email" placeholder="Username or e-mail address">
							</div>
							<div class="form-group password">
								<input type="txt_password" class="form-control" id="password" name="txt_password" placeholder="Password">
							</div>
							<button type="submit" class="btn btn-primary d-block text-center mb-2">Sign In</button>
							<a href="aff_help.php">Forgot Password?</a>
						</form>
					</div>
				</div>
			</div>
		</div>

	</body>
</html>
