<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 1/11/2018
 * Time: 3:34 PM
 */


$webroot = getWebRoot();

$mid = (isset($_GET["mid"]) && $_GET["mid"] != "") ? $_GET["mid"] : "";

?>

<!DOCTYPE html>
<html>
<head>
	
	<meta http-equiv = "Content-Type" content = "text/html; charset=utf-8"/>
	<meta name = "viewport" content = "width=device-width, initial-scale=1">
	
	<link rel = "shortcut icon" type = "image/ico"
		  href = "<?PHP echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() . "/favicon.ico"; ?>"/>
	<link rel = "shortcut icon" type = "image/ico"
		  href = "<?PHP echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() . "/favicon.ico"; ?>"/>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

	<link href = "css/animate.css" rel = "stylesheet">
	
	
	<link rel = "stylesheet" type = "text/css" href = "<?php echo $webroot; ?>css/default.css"/>
	
	<link rel = "stylesheet" media = "screen" type = "text/css"
		  href = "<?php echo $webroot; ?>css/company.css"/>
	
	
	<link rel = "stylesheet" type = "text/css" href = "<?php echo $webroot; ?>css/font-awesome.min.css">
	<link rel = "stylesheet" href = "<?php echo $webroot; ?>css/magic.min.css">
	
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/jquery_2.1.3_jquery.min.js"></script>
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/jquery-ui.min.js"></script>
	
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/jscolor.min.js"></script>
	<link rel = "stylesheet" href = "css/jquery-ui.min.css"/>
	
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/tables.js"></script>
	<script type = "text/javascript" src = "<?php echo $webroot; ?>js/bootstrap-notify.min.js"></script>
	
	
	<title><?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?></title>
</head>
<body style = "background-color:#EAEEF1;">
<div class = "top_sec value_span1">
	<div class = "logo">
		<a href = "<?php echo $webroot ?>"><img src = "<?=\LeadMax\TrackYourStats\System\Company::loadFromSession()->getImgDir() ?>/logo.png" alt = "TRACK YOUR STATS"
												title = "TRACK YOUR STATS"/></a>
	</div>

</div> <!-- top_sec -->

<style>
	
	.white_box {
		
		margin-top: 40px;
	}
	
	.white_box_outer {
		float: none;
		margin: 0 auto;
		max-width: 750px;
	}
	
	.left_con01 {
		width: auto;
		padding: 5px;
		padding-top: 10px;
		padding-left: 17px;
		padding-right: 10px;
		float: none;
		
	}
	
	.heading_holder {
		margin: 0px 0px 10px 0px;
	}
</style>


	   <!--right_panel-->
<div class = "white_box_outer">
	
	<div class = "clear"></div>
	<div class = "white_box value_span8">
		<div class = "com_acc">
			
			<form action = "/" id = "signUpForm">
				<div class = "left_con01">
					<div class = "heading_holder">
						<h3 class = " value_span9">Sign Up</h3>
					</div>
					<input name="mid" type="hidden" value="<?php echo $mid; ?>">
					
					<p>
						<label class = "value_span9" for = "tys_first_name">First Name: <sup>*</sup></label>
						<input class = "form-control" type = "text" name = "tys_first_name">
					</p>
					
					<p>
						
						<label for = "tys_last_name">Last Name: <sup>*</sup></label>
						<input type = "text" name = "tys_last_name">
					</p>
					
					
					<p>
						
						<label for = "tys_email">Email: <sup>*</sup></label>
						<input type = "text" name = "tys_email">
					</p>
					
					
					<p>
						
						<!-- MUST BE GREATER THAN FOUR CHARACTERS -->
						<label for = "tys_username">Username: <sup>*</sup></label>
						<input type = "text" name = "tys_username">
						<small>(MUST BE GREATER THAN 4 CHARACTERS)</small>
					</p>
					
					<p>
						
						<!-- MUST BE GREATER THAN 6 CHARACTERS -->
						<label for = "tys_password">Password: <sup>*</sup></label>
						<input type = "password" name = "tys_password">
						<small>(MUST BE GREATER THAN 6 CHARACTERS)</small>
					</p>
					
					
					<p>
						
						<label for = "tys_confirm_password">Confirm Password: <sup>*</sup></label>
						<input type = "password" name = "tys_confirm_password">
					</p>
					
					
					<p>
						
						<label for = "tys_company_name">Company:</label>
						<input type = "text" name = "tys_company_name">
					</p>
					
					
					<p>
						
						<label for = "tys_skype">Skype:</label>
						<input type = "text" name = "tys_skype">
					
					</p>
					
					
					<span class = "btn_yellow" style = "color:#1D4C9E;">
						<input type = "submit" name = "button" class="rounded-5 value_span5-1 value_span2 value_span4" value = "Sign Up"/>
					</span>
				
				</div>
				<div class = "right_con01">
				
				</div>
			</form>
		</div><!-- white_box -->
	</div><!-- white_box_outer -->
	<script type = "text/javascript">
		
		function notify(message, type) {
			
			$.notify({
					
					message: message
					
				}, {
					placement: {
						from: 'top',
						align: 'center'
					},
					type: type,
					animate: {
						enter: 'animated fadeInDown',
						exit: 'animated fadeOutUp'
					},
				}
			);
		}
		
		
		function handleResponse(responseCode) {
			
			responseCode = responseCode.replace(/\s/g, '');
			
			switch (responseCode) {
				case "SUCCESS"    :
					let mid = '<?php echo $mid; ?>';
					mid = mid === "" ? "" : '?mid=true';

					window.location = 'signup_success.php' + mid;
					break;
				
				case "USERNAME_OR_EMAIL_EXISTS" :
					notify("The username or email you entered already exists in the system.", 'warning');
					break;
				
				case "INVALID_EMAIL":
					notify("The email you entered is invalid.", 'warning');
					break;
				
				case "INVALID_USERNAME":
					notify("The username you entered is invalid, please make sure it is at least 4 characters long, and contains no special characters.", 'warning');
					break;
				
				case "PASSWORD_MISMATCH":
					notify("Password do not match.", 'warning');
					break;
				
				case "MISSING_OR_INVALID_FIELDS":
					notify("You have missing fields or they are invalid, please double check them", 'warning');
					break;
				
				default :
					notify("Unknown error. Please contact an administrator is this persists.", 'danger');
					break;
			}
			
		}
		
		
		$("#signUpForm").on('submit', function (event) {
			
			// stops form from submitting
			event.preventDefault();
			
			let postData = $("#signUpForm").serialize();

			$.ajax({
				type: "post",
				url: "scripts/affiliate_signup.php",
				data: postData,
				success: function (responseData, textStatus, jqXHR) {
					handleResponse(responseData);
				},
				error: function (jqXHR, textStatus, errorThrown) {
					alert(errorThrown);
				}
			});
		});
	
	</script>
	
	
	<?php include 'footer.php'; ?>


</html>











