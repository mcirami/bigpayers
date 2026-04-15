<?php

$section = "offers-list";
require('header.php');


//Call Class
$na = new \LeadMax\TrackYourStats\Offer\Offer();
//Select one record
if (isset($_GET['idoffer']))
{
	$rows = $na->SelectOne($_GET['idoffer']);
}


?>
	
	
	<!--right_panel-->
	<div class = "right_panel">
	<div class = "white_box_outer">
		<div class = "heading_holder value_span9"><span class = "lft">Offer <?php echo $_GET['idoffer']; ?>  </span></div>
		<div class = "white_box value_span8">
			
			
			<div class = "left_con01">
				<p>
					<label class = "value_span9">Name</label>
					<input disabled type = "text" class = "form-control" name = "offer_name" maxlength = "155"
						   value = "<?php echo $rows->offer_name; ?>" id = "offer_name"/>
				</p>
				<p>
					<label class = "value_span9">Status</label>
					
					<?php if ($rows->status == 1)
					{
						
						echo "<select disabled class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"1\"><option selected value=\"1\">Active</option>;<option value=\"0\">Disabled</option>;</select>";
					}
					else
					{
						
						echo "<select disabled class=\"form-control input-sm \" id=\"status\" name=\"status\" value=\"1\"><option value=\"1\">Active</option>;<option selected value=\"0\">Disabled</option>;</select>";
						
					}
					?>
				
				
				</p>
				<p>
					<label class = "value_span9">Description</label>
					<input disabled type = "text" class = "form-control" name = "description" maxlength = "555"
						   value = "<?php echo $rows->description; ?>" id = "description"/>
				</p>
				<p>
					<label class = "value_span9">URL</label>
					<input disabled type = "text" class = "form-control" name = "url" maxlength = "555"
						   value = "<?php echo $rows->url; ?>" id = "url"/>
					<span class = "small_txt value_span10">The offer URL where traffic will be directed to. The variables below can be used in offe URLs.</span>
					<span class = "small_bold_txt value_span10">s1=#repid#&s5=#clickid#</span>
				</p>
			
			</div>
			<div class = "right_con01">
				<p>
					<label class = "value_span9">Payout</label>
					
					<input type = "text" disabled class = "form-control" name = "payout" maxlength = "12"
						   value = "<?php echo $rows->payout; ?>" id = "payout"/>
					<span class = "small_txt value_span10">The Amount paid to affiliates per conversion</span></p>
				
				<?php
				
				if (\LeadMax\TrackYourStats\System\Session::userType() != \App\Privilege::ROLE_AFFILIATE && \LeadMax\TrackYourStats\System\Session::userType() != \App\Privilege::ROLE_UNKNOWN)
				{
					
					echo "<p><label for=\"affiliateOwner\"> Assigned To</label>


                        <select multiple class=\"form-control input-sm\">";
					
					$idoffer = $_GET['idoffer'];
					
					$new_replist = new \LeadMax\TrackYourStats\User\User();
					$allReps     = $new_replist->select_all_num()->fetchAll(PDO::FETCH_ASSOC);
					
					$new_RepHasOffer = new \LeadMax\TrackYourStats\Offer\RepHasOffer();
					$assignedReps    = $new_RepHasOffer->selectAllAssignedReps($idoffer)->fetchALL(PDO::FETCH_ASSOC);
//                            print_x($assignedReps);
					$db   = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
					$sql  = "SELECT rep_idrep FROM rep_has_offer WHERE offer_idoffer = :offerid";
					$prep = $db->prepare($sql);
					$prep->bindParam(":offerid", $idoffer);
					$prep->execute();
					$repIDsWithOffer = $prep->fetchAll(PDO::FETCH_NUM);
					
					//                            foreach ($allReps as $key => $value) {
					//
					//                                $first_name = $value["first_name"];
					//                                $idrep = $value["idrep"];
					//                                echo $value;
					
					$newCount = array();
					
					//parses multi dimential array into just normal array with repIDs
					for ($i = 0; $i < count($repIDsWithOffer); $i++)
					{
						array_push($newCount, $repIDsWithOffer[$i][0]);
					}
					
					
					$plebs = array();
					
					for ($nark = 0; $nark < count($allReps); $nark++)
					{
						
						//TODO Optimize check privileges
						/*TODO Query privileges table to return repids where is_god = 1, then parse into one dimential array like above, then in_array() compare with repid ($allReps[$nark][0]); */
						if ($allReps[$nark][0] != 1)
						{
							
							if (in_array($allReps[$nark][0], $newCount))
							{
								echo "<option disabled selected value='{$allReps[$nark][0]}' > {$allReps[$nark]['user_name']} </option>";
							}
							else
							{
								echo "<option disabled value='{$allReps[$nark][0]}' > {$allReps[$nark]['user_name']} </option>";
							}
							
							
						}
						
						
					}
					echo "</select></p>";
					
				}
				
				//                            echo "<p><label for=\"affiliateOwner\"> Assign To</label>
				//                    <select class=\"form-control input-sm\" id=\"replist\" name=\"replist[]\" multiple=\"\">";
				//                            $idoffer = $_GET['idoffer'];
				//
				//                            $new_replist = new User();
				//                            $allReps = $new_replist->select_all();
				//
				//                            $new_RepHasOffer = new rep_has_offer();
				//                            $assignedReps = $new_RepHasOffer->selectAllAssignedReps($idoffer);
				//                            print_x($assignedReps);
				//
				//                            foreach ($allReps as $key => $value) {
				//
				//                                $first_name = $value["first_name"];
				//                                $idrep = $value["idrep"];
				//                                echo $value;
				//                                echo "<option disabled value='$idrep' > $first_name  </option>";
				//                            }
				//                            echo "     </select>";
				//
				//                        }
				
				?>
				
				
				<p style = "margin-top:10px;">
					
					<label class = "value_span9">Offer Timestamp</label>
					
					<input type = "text" class = "form-control" name = "offer_timestamp" maxlength = "19"
						   value = "<?php echo $rows->offer_timestamp; ?>" id = "offer_timestamp" disabled/>
				</p>
			
			
			</div>
		
		
		</div>
	
	
	</div>
	
	
	<!--right_panel-->

<?php include "footer.php"; ?>