<?php $section = "notifications"; ?>
<?php include 'header.php';


$assign = new \LeadMax\TrackYourStats\Table\Assignments(['action' => null, 'id' => null]);
$assign->getAssignments();

$notifications = new \LeadMax\TrackYourStats\System\Notifications(\LeadMax\TrackYourStats\System\Session::userID());
$notifications->processAction($assign);

$notifications->fetchUsersNotifications();

?>

    <!--right_panel-->
    <div class="right_panel">
        <div class="white_box_outer ">
            <div class="heading_holder">
                <span class="lft value_span9">Notifications</span>
                <?php
                if (\LeadMax\TrackYourStats\System\Session::permissions()->can("create_notifications"))
                    echo "<a style='margin-left: 1%; margin-top:.3%;' href=\"create_notification.php\" class='btn btn-default btn-sm'><img src='/images/icons/add.png' >&nbsp;Create Notification</a>";

                ?>
            </div>
            <div class="white_box_x_scroll white_box value_span8" >

                <?php

                    if($assign->get("action") == null)
                    {
                        echo " <table class=\"table-sm table table-bordered  \" id=\"mainTable\"'>
                                <thead>
                                <tr>
                                    <td>Title</td>
                                    <td>Body</td>
                                    <td>Date</td>
                                    <td>Author</td>
                                    <td>Actions</td>
                                </tr>
                                </thead>
            
                                    <tbody>
                                   ";

                                          $notifications->printToTable();


                               echo "</tbody>

                             </table>";
                    }
                    else if($assign->get("action") !== 'view')
                        echo "<h3>Processing...</h3>";



                    switch($assign->get("action"))
                    {
                        case "view":
                            $notifications->view($assign->get("id"));
                            break;

                        case "mark":
                            $notifications->markAndRedirect($assign->get("id"));
                            break;


                        case "delete":
                            $notifications->deleteAndRedirect($assign->get("id"));
                            break;


                    }


                ?>


                <script type="text/javascript">
                    function confirmPlease(id)
                    {
                        var result = confirm("Are you sure you want to delete this?");
                        if(result)
                            window.location = "notifications.php?action=delete&id="+id;


                    }

                </script>
<!--                <div class="notifications">-->
<!--                    <div class="row">-->
<!--                        <a href="#">-->
<!--                            <div class="checkbox">-->
<!--                                <input class="notif_check" type="checkbox">-->
<!--                            </div>-->
<!--                            <div class="from">-->
<!--                                <p>Jack Hoff</p>-->
<!--                            </div>-->
<!--                            <div class="subject">-->
<!--                                <p>Lorem ipsum dolar imit set.</p>-->
<!--                            </div>-->
<!--                            <div class="time">-->
<!--                                <p>6:10 AM</p>-->
<!--                            </div>-->
<!--                        </a>-->
<!--                    </div>-->
<!--                    <div class="row">-->
<!--                        <a href="#">-->
<!--                            <div class="checkbox">-->
<!--                                <input class="notif_check" type="checkbox">-->
<!--                            </div>-->
<!--                            <div class="from">-->
<!--                                <p>IP Freely</p>-->
<!--                            </div>-->
<!--                            <div class="subject">-->
<!--                                <p>Lorem ipsum dolar imit set.</p>-->
<!--                            </div>-->
<!--                            <div class="time">-->
<!--                                <p>7:10 PM</p>-->
<!--                            </div>-->
<!--                        </a>-->
<!--                    </div>-->
<!--                    <div class="row">-->
<!--                        <a href="#">-->
<!--                            <div class="checkbox">-->
<!--                                <input class="notif_check" type="checkbox">-->
<!--                            </div>-->
<!--                            <div class="from">-->
<!--                                <p>Ed Ved</p>-->
<!--                            </div>-->
<!--                            <div class="subject">-->
<!--                                <p>Lorem ipsum dolar imit set.</p>-->
<!--                            </div>-->
<!--                            <div class="time">-->
<!--                                <p>9:10 AM</p>-->
<!--                            </div>-->
<!--                        </a>-->
<!--                    </div>-->
<!--                </div>-->
            </div><!-- white_box -->
        </div><!-- white_box_outer -->
    </div>
    <!--right_panel-->
	
	
	<script type = "text/javascript">
		
		$(document).ready(function () {
			$("#mainTable").tablesorter(
				{
					sortList: [[2,1]],
					widgets: ['staticRow']
				});
		});
	</script>

<?php include 'footer.php'; ?>