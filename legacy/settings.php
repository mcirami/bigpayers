<?php


include 'header.php';
if (post("button")) {
	$colors = "";
	$colors .= post("valueSpan1");
	$colors .= ";" . post("valueSpan2");
	$colors .= ";" . post("valueSpan3");
	$colors .= ";" . post("valueSpan4");
	$colors .= ";" . post("valueSpan5");
	$colors .= ";" . post("valueSpan6");
	$colors .= ";" . post("valueSpan7");
	$colors .= ";" . post("valueSpan8");
	$colors .= ";" . post("valueSpan9");
	$colors .= ";" . post("valueSpan10");
	$colors .= ";" . post("valueSpan11");

	$company =\LeadMax\TrackYourStats\System\Company::loadFromSession();

	$company->updateCompany(post("shortHand"), $colors, \post("email"), \post('skype'), \post('loginURL'), \post('landingPage'));

	send_to(parse_url($_SERVER["REQUEST_URI"])["path"]);
}


$colors = \LeadMax\TrackYourStats\System\Company::loadFromSession()->getColors();


?>


<script type="text/javascript">
	function _(el) {
		return document.getElementById(el);
	}

	function uploadFile() {
		var file = _("file1").files[0];
		//alert(file.name+" | "+file.size+" | "+file.type);
		var formdata = new FormData();
		formdata.append("file1", file);
		var ajax = new XMLHttpRequest();
		ajax.upload.addEventListener("progress", progressHandler, false);
		ajax.addEventListener("load", completeHandler, false);
		ajax.addEventListener("error", errorHandler, false);
		ajax.addEventListener("abort", abortHandler, false);
		ajax.open("POST", "upload_logo.php");
		ajax.send(formdata);
	}

	function progressHandler(event) {
		_("loaded_n_total").innerHTML = "Uploaded " + event.loaded + " bytes of " + event.total;
		var percent = (event.loaded / event.total) * 100;
		_("progressBar").value = Math.round(percent);
		_("status").innerHTML = Math.round(percent) + "% file uploaded... PLEASE WAIT TILL DONE!!!";
	}

	function completeHandler(event) {
		_("status").innerHTML = event.target.responseText;
		_("progressBar").value = 100;
		_("status").innerHTML = "DONE! - UPLOADING ";
	}

	function errorHandler(event) {
		_("status").innerHTML = "Upload Failed";
	}

	function abortHandler(event) {
		_("status").innerHTML = "Upload Aborted";
	}

	function uploadFile2() {
		var file = _("file2").files[0];
		//alert(file.name+" | "+file.size+" | "+file.type);
		var formdata = new FormData();
		formdata.append("file2", file);
		var ajax = new XMLHttpRequest();
		ajax.upload.addEventListener("progress", progressHandler2, false);
		ajax.addEventListener("load", completeHandler2, false);
		ajax.addEventListener("error", errorHandler2, false);
		ajax.addEventListener("abort", abortHandler2, false);
		ajax.open("POST", "upload_favicon.php");
		ajax.send(formdata);
	}

	function progressHandler2(event) {
		_("loaded_n_total2").innerHTML = "Uploaded " + event.loaded + " bytes of " + event.total;
		var percent = (event.loaded / event.total) * 100;
		_("progressBar2").value = Math.round(percent);
		_("status2").innerHTML = Math.round(percent) + "% file uploaded... PLEASE WAIT TILL DONE!!!";
	}

	function completeHandler2(event) {
		_("status2").innerHTML = event.target.responseText;
		_("progressBar2").value = 100;
		_("status2").innerHTML = "DONE! - UPLOADING ";
	}

	function errorHandler2(event) {
		_("status2").innerHTML = "Upload Failed";
	}

	function abortHandler2(event) {
		_("status2").innerHTML = "Upload Aborted";
	}


</script>

<!--right_panel-->
<div class="right_panel">
	<div class="white_box_outer">
		<div class="heading_holder">
			<span class="lft value_span9">Setting</span>
		</div>
		<div class="white_box value_span8">
			<!-- style=" pointer-events: none;
opacity: 0.4; " -->
			<form id="form" method="post" action="<?php echo htmlspecialchars(parse_url($_SERVER["REQUEST_URI"])["path"]); ?>">
				<div class="column_wrap">
					<div class="left_con01 value_span7">


						<h2 class="value_span9">Interface Design</h2>
						<!--                    <h2 class="small_txt value_span3-1">Edit Theme</h2>-->
						<p class="value_span10">Customize your theme's colors by using the color selector.</p>


						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color1" id="stylePreview1">&nbsp;</span>
							<span class="col2">
		                        <input name="valueSpan1"
		                               type="text"
		                               id="valueSpan1"
		                               class="jscolor {valueElement:'valueSpan1',styleElement:'stylePreview1'}"
		                               style="position:relative; width:100%; background:#fff; border:none;"
		                               value="<?php echo $colors[0]; ?>">
							</span>
							<span class="col3 value_span9">Top Header &amp; Nav Selected</span></div>
						<!--colorcode-->
						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color2" id="stylePreview2">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan2"
								       id="valueSpan2"
								       class="jscolor {valueElement:'valueSpan2',styleElement:'stylePreview2'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[1]; ?>"></span>
							<span class="col3 value_span9">Nav Sub Menu Text Color, Button Text Color, Sidebar Label Color</span>
						</div>
						<!--colorcode-->

						<!--colorcode-->
						<div class="color_code_h value_span7"><span class="col1 color3" id="stylePreview3">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan3"
								       id="valueSpan3"
								       class="jscolor {valueElement:'valueSpan3',styleElement:'stylePreview3'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[2]; ?>">
							</span>
							<span class="col3 value_span9">Left Nav &amp; Sub Title Text Color</span>
						</div>
						<!--colorcode-->

						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color8" id="stylePreview11">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan11"
								       id="valueSpan11"
								       class="jscolor {valueElement:'valueSpan11',styleElement:'stylePreview11'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[10]; ?>"></span>
							<span class="col3 value_span9">Main Button Color</span>
						</div>
						<!--colorcode-->
						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color4" id="stylePreview4">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan4"
								       id="valueSpan4"
								       class="jscolor {valueElement:'valueSpan4',styleElement:'stylePreview4'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[3]; ?>">
							</span>
							<span class="col3 value_span9">Nav SubMenu Hover & Select & Main Button Hover</span>
						</div>
						<!--colorcode-->

						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color5" id="stylePreview5">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan5"
								       id="valueSpan5"
								       class="jscolor {valueElement:'valueSpan5',styleElement:'stylePreview5'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[4]; ?>"></span>
							<span class="col3 value_span9">Nav Menu Text Color, Link Text Color</span>
						</div>
						<!--colorcode-->

						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color6" id="stylePreview6">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan6"
								       id="valueSpan6"
								       class="jscolor {valueElement:'valueSpan6',styleElement:'stylePreview6'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[5]; ?>"></span>
							<span class="col3 value_span9">Menu Hover & Select Accent Color</span>
						</div>

						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color7" id="stylePreview7">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan7"
								       id="valueSpan7"
								       class="jscolor {valueElement:'valueSpan7',styleElement:'stylePreview7'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[6]; ?>"></span>
							<span class="col3 value_span9">Main Area and Content Sub Box Background Color</span>
						</div>
						<!--colorcode-->

						<!--colorcode-->
						<div class="color_code_h value_span7">
							<span class="col1 color8" id="stylePreview8">&nbsp;</span>
							<span class="col2">
								<input name="valueSpan8"
								       id="valueSpan8"
								       class="jscolor {valueElement:'valueSpan8',styleElement:'stylePreview8'}"
								       style="position:relative; width:100%; background:#fff; border:none;"
								       value="<?php echo $colors[7]; ?>"></span>
							<span class="col3 value_span9">Content Area Background Color</span>
						</div>
						<!--colorcode-->

						<!--colorcode-->
						<div class="color_code_h value_span7"><span class="col1 color8" id="stylePreview9">&nbsp;</span>
							<span class="col2"><input name="valueSpan9" id="valueSpan9"
							                          class="jscolor {valueElement:'valueSpan9',styleElement:'stylePreview9'}"
							                          style="position:relative; width:100%; background:#fff; border:none;"
							                          value="<?php echo $colors[8]; ?>"></span> <span
									class="col3 value_span9">Content Area Title Text Color</span></div>
						<!--colorcode-->

						<div class="color_code_h value_span7"><span class="col1 color8"
						                                            id="stylePreview10">&nbsp;</span> <span
									class="col2"><input name="valueSpan10" id="valueSpan10"
						                                class="jscolor {valueElement:'valueSpan10',styleElement:'stylePreview10'}"
						                                style="position:relative; width:100%; background:#fff; border:none;"
						                                value="<?php echo $colors[9]; ?>"></span> <span
									class="col3 value_span9">Content Area Sub Text Color</span></div>
						<!--colorcode-->


						<a style="width: 100%; display: block;" id="default" class="value_span5" href="#">Reset To Default</a>

						<!--<a href="#">Save</a>-->
						</span>


					</div><!-- left_con01 -->

					<div class="right_con01 setting value_span7">
						<p>

							<label class="value_span9">Name:</label>
							<input name="shortHand" placeholder="Track Your Stats" onfocus="this.placeholder = ''"
							       onblur="this.placeholder = '<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>'" type="text"
							       value="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getShortHand(); ?>">
							<span class="small_txt value_span10">This name is displayed throughout the application, including all emails, notifications, and in the header.</span>

						</p>
						<p>

							<label class="value_span9">Skype:</label>
							<input name="skype" placeholder="" onfocus="this.placeholder = ''"
							       type="text"
							       value="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getSkype(); ?>">

						</p>
						<p>

							<label class="value_span9">Email:</label>
							<input name="email" placeholder="" onfocus="this.placeholder = ''"
							       type="text"
							       value="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getEmail(); ?>">

						</p>

						<p>
							<label class="value_span9">Login URL:</label>
							<input name="loginURL" placeholder="" onfocus="this.placeholder = ''"
							       type="text"
							       value="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getLoginURL(); ?>">
						</p>

						<p>
							<label class="value_span9">Landing Page:</label>
							<input name="landingPage" placeholder="" onfocus="this.placeholder = ''"
							       type="text"
							       value="<?php echo \LeadMax\TrackYourStats\System\Company::loadFromSession()->getLandingPage(); ?>">
						</p>


						<form id="upload_form" enctype="multipart/form-data" method="post">
							<p>
								<label class="value_span9">Logo</label>
								<input type="file" name="file1" id="file1" accept="image/*"><br>

								<span class="small_txt value_span10">File must be a .png </span>
							</p>
							<input type="button" value="Upload File" onclick="uploadFile()">
							<progress id="progressBar" value="0" max="100" style="width:300px;"></progress>
							<h3 id="status"></h3>


							<p id="loaded_n_total"></p>

						</form>


						<br/>
						<br/>
						<form id="upload_form" enctype="multipart/form-data" method="post">
							<p>
								<label class="value_span9">Custom Favicon</label>
								<input type="file" name="file2" id="file2" accept="image/*"><br>

								<span class="small_txt value_span10">File must be an icon (.ico)</span>
							</p>
							<input type="button" value="Upload File" onclick="uploadFile2()">
							<progress id="progressBar2" value="0" max="100" style="width:300px;"></progress>
							<h3 id="status2"></h3>


							<p id="loaded_n_total2"></p>
						</form>

					</div><!-- right_con01 setting -->
					<span class="btn_yellow">
				        <input id="button"
				               name="button"
				               style="margin-top:50px;"
				               class="value_span11 value_span2 value_span4"
				               type="submit"
				               value="Save">
					</span>
				</div><!-- column_wrap -->
			</form>
		</div><!-- white_box -->
	</div><!-- white_box_outer -->
</div>
	<!--right_panel-->

	<?php include 'footer.php'; ?>
