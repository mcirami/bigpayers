<?php

$section = "offers-edit-pb";
require('header.php');


if (!isset($_GET["offid"]))
{
	send_to("home.php");
}

if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_AFFILIATE)
{
	send_to('home.php');
}

//verify User has this offer


$offid = filter_var($_GET["offid"], FILTER_SANITIZE_NUMBER_INT);


use LeadMax\TrackYourStats\User\PostBackURLs;

$conversionPostBack = new PostBackURLs\ConversionPostBackURL(\LeadMax\TrackYourStats\System\Session::userID(), $offid);
$deductionPostBack  = new PostBackURLs\DeductionPostBackURL(\LeadMax\TrackYourStats\System\Session::userID(), $offid);
$freePostBack       = new PostBackURLs\FreePostBackURL(\LeadMax\TrackYourStats\System\Session::userID(), $offid);


if (post("button"))
{
	
	$conversionPostBack->updateOfferURL($_POST["postback_url"]);
	$deductionPostBack->updateOfferURL($_POST["deduction_url"]);
	$freePostBack->updateOfferURL($_POST["free_sign_up_url"]);
	
	send_to("offer_edit_pb.php?offid={$offid}");
}

?>
	
	<!--right_panel-->
	<div class = "right_panel">
		<div class = "white_box_outer large_table">
			<div class = "heading_holder">
				<span class = "lft value_span9">Edit Postback Options for Offer - <?= $offid ?></span>
			
			</div>
			
			
			<div class = "clear"></div>
			<form action = "<?= parse_url($_SERVER["REQUEST_URI"])["path"] . "?offid=" . $offid ?>" method = "POST">
				
				<div class = "white_box value_span8">
					
					<div class = "left_con01">
						
						<!--                <p>-->
						<!--                    <label class="value_span9">Append to Offer URL:</label>-->
						<!---->
						<!--                    <input  type="text" class="form-control input-sm" name="append_url" maxlength="155"-->
						<!--                            value="--><? //=$result[0]?><!--" id="append_url"/>-->
						<!--                    <span class="small_txt value_span10">Ex: "&sub1=1234", when a click registers, sub1 is stored into our db,<br/> On post back fire, you can access sub1 like so, #sub1#. Only sub1-sub5 are available.</span>-->
						<!---->
						<!--                </p>-->
						
						
						<p>
							<label class = "value_span9">Conversion PostBack URL: (Conversion fires)</label>
							
							<input type = "text" class = "form-control input-sm" name = "postback_url" maxlength = "155"
								   value = "<?= $conversionPostBack->getOfferSpecificURL(); ?>" id = "postback_url"/>
						</p>
						
						<p>
							<label class = "value_span9">Free Sign Up PostBack URL: ( &function=free )</label>
							
							<input type = "text" class = "form-control input-sm" name = "free_sign_up_url" maxlength = "155"
								   value = "<?= $freePostBack->getOfferSpecificURL() ?>" id = "postback_url"/>
						</p>
						
						
						<p>
							<label class = "value_span9">Deduction PostBack URL: ( &function=deduct )</label>
							
							<input type = "text" class = "form-control input-sm" name = "deduction_url" maxlength = "155"
								   value = "<?= $deductionPostBack->getOfferSpecificURL() ?>" id = "deduction_url"/>
						</p>
						
						<p>
							<label class = "value_span9">TrackYourStats Vars</label>
							<span>#affid#</span><br/>
							<span>#user#</span><br/>
							<span>#offid#</span> <br/>
							<span>#clickid#</span> <br/>
							
							<span class = "small_txt value_span10">TrackYourStats vars are auto inputed into your URL if they're found.</span>
							<span class = "small_txt value_span10">e.g. "google.com/?var1=<b>#affid#"</b> will translate to "google.com/?var1=<b>32</b>"</span>
							
							<br/>
							<br/>
							<span class = "small_txt value_span10">To store sub vars, have your software append to our offer url with sub1-sub3. Ex: &sub1=3213</span>
							<span class = "small_txt value_span10">Sub vars can be used in postback url as such:  #sub1#,     #sub2#    etc. </span>
							
							<br/>
							<br/>
							<br/>
							<span class = "small_txt value_span10">When getting a url, if not otherwise specified, it is correct convention to seperate additionally vars with an ampersand </span>
							<br/>
							<span class = "small_txt value_span10">Ex: http://google.com/?var1=#sub1#<b>&</b>var2=#sub2#</span>
						
						
						</p>
					
					
					</div>
					<div class = "right_con01">
						<!--                <p>-->
						<!--                    <label class="value_span9">Sub 1:</label>-->
						<!---->
						<!--                    <input type="text" class="form-control input-sm" name="sub1" maxlength="155"-->
						<!--                           value="--><? //=$result[1]?><!--" id="sub1"/>-->
						<!--                </p>-->
						<!---->
						<!---->
						<!--                <p>-->
						<!--                    <label class="value_span9">Sub 2:</label>-->
						<!---->
						<!--                    <input type="text" class="form-control input-sm" name="sub2" maxlength="155"-->
						<!--                           value="--><? //=$result[2]?><!--" id="sub2"/>-->
						<!--                </p>-->
						<!---->
						<!--                <p>-->
						<!--                    <label class="value_span9">Sub 3:</label>-->
						<!---->
						<!--                    <input type="text" class="form-control input-sm" name="sub3" maxlength="155"-->
						<!--                           value="--><? //=$result[3]?><!--" id="sub3"/>-->
						<!--                </p>-->
						<!---->
					
					
					</div>
				
				
				</div>
				<span class = "btn_yellow"> <input type = "submit" name = "button"
												   class = "value_span6-2 value_span2 value_span1-2"
												   value = "Update"/></span>
			</form>
		
		
		</div>
	
	</div>

<?php include 'footer.php'; ?>