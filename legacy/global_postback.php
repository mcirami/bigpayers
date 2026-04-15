<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 2/6/2018
 * Time: 3:37 PM
 */
include 'header.php';

if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_AFFILIATE)
{
	send_to('home.php');
}

$postBackUrls = new \LeadMax\TrackYourStats\User\PostBackUrl(\LeadMax\TrackYourStats\System\Session::userID());

if (isset($_POST["postback_url"]))
{
	$result = \LeadMax\TrackYourStats\User\PostBackUrl::updateUserPostBacks(\LeadMax\TrackYourStats\System\Session::userID(), $_POST["postback_url"]);

	if ($result)
	{
		send_to('global_postback.php');
	}


}


?>

<!--right_panel-->
<div class = "right_panel">
	<div class = "white_box_outer large_table">
		<div class = "heading_holder">
			<span class = "lft value_span9">Global PostBack</span>

		</div>


		<div class = "clear"></div>
		<form action = "global_postback.php" method = "POST">

			<div class = "white_box value_span8">

				<div class = "left_con01">


					<p>
						<label class = "value_span9">Global PostBack URL: On Conversion</label>

						<input type = "text" class = "form-control input-sm" name = "postback_url" maxlength = "155"
							   value = "<?= \LeadMax\TrackYourStats\User\User::getUsersGlobalPostBackURL(\LeadMax\TrackYourStats\System\Session::userID()) ?>" id = "postback_url"/>
						<span class = "small_txt value_span10">If you do not enter a post back url on an offer, this url will automatically trigger for each conversion.</span>
					</p>

					<p>
						<label class = "value_span9">Variables To Choose From:</label>
						<span>#affid#</span><br/>
<!--						<span>#user#</span><br/>   -->
						<span>#offid#</span> <br/>
						<span>#clickid#</span> <br/>

						<span class = "small_txt value_span10">Variables are auto inputed into your URL if they are found.</span>
						<span class = "small_txt value_span10">Example: "domain.com/?var1=<b>#affid#"</b> will translate to "domain.com/?var1=<b>AffiliateID</b>"</span>

						<br/>
						<br/>
						<span class = "small_txt value_span10">To store additional variables, you can add sub1= through sub5= to the offer url. When adding multiple variables to an offer url, seperate each variable with an ampersand like this: </span>
						<br/>
						<span class = "small_txt value_span10">Example: http://domain.com/?var1=#sub1#<b>&</b>var2=#sub2#</span>

					</p>


					<span class = "btn_yellow"> <input type = "submit" name = "button"
													   class = "value_span6-2 value_span2 value_span1-2"
													   value = "Save"/></span>
				</div>
				<div class = "right_con01" id = "offers">


				</div>


			</div>
		</form>


	</div>

</div>

<?php include 'footer.php'; ?>
