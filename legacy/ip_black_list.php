<?php
/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/27/2017
 * Time: 12:20 PM
 *
 */

$section = "ip-blacklist";

require('header.php');


if (\LeadMax\TrackYourStats\System\Session::userType() !== \App\Privilege::ROLE_GOD)
    send_to("home.php");

if(isset($_GET["action"]) && isset($_GET["id"]))
{
    if($_GET["action"] == "delete")
        if(\LeadMax\TrackYourStats\System\IPBlackList::deleteBlackList($_GET["id"]))
            send_to("ip_black_list.php");
}

$ips = \LeadMax\TrackYourStats\System\IPBlackList::selectIPs()->fetchAll(PDO::FETCH_OBJ);

?>

<!--right_panel-->
<div class="right_panel">
    <div class="white_box_outer large_table ">
        <div class="heading_holder">
            <span class="lft value_span9">Black Listed IP Addresses</span>
            <a href="add_new_ip_blacklist.php" class="btn btn-default">Add New IP Range</a>

        </div>

        <div class="white_box_x_scroll white_box manage_aff large_table value_span8 ">
            <table class="table table-bordered table_01" id="mainTable">
                <thead>

                <tr>
                    <th class="value_span9">ID</th>
                    <th class="value_span9">Start Range</th>
                    <th class="value_span9">End Range</th>
                    <th class="value_span9">Created On</th>
                    <th class="value_span9">Actions</th>


                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($ips as $ip) {
                    echo "<tr>";
                    echo "<td>{$ip->id}</td>";
                    echo "<td>" . long2ip($ip->start) . "</td>";
                    echo "<td>" . long2ip($ip->end) . "</td>";
                    echo "<td>" . \Carbon\Carbon::createFromFormat("U", $ip->timestamp)->toFormattedDateString() . "</td>";
                    echo "<td><a href='edit_blacklisted_ip.php?id={$ip->id}' class='btn btn-default btn-sm'>Edit</a></td>";
                    echo "<td><a href='javascript:void(0);' onclick='isufosho({$ip->id});' class='btn btn-default btn-sm'>Delete</a></td>";
                    echo "</tr>";
                }

                ?>

                </tbody>
            </table>

        </div>
    </div>
</div>
<!--right_panel-->
<script type="text/javascript">
    function isufosho(id)
    {
        if(confirm("Are you sure you want to delete this fam?"))
            window.location = 'ip_black_list.php?action=delete&id=' + id;
    }
</script>



<script type = "text/javascript">
	
	$(document).ready(function () {
		$("#mainTable").tablesorter(
			{
				sortList: [[3,1]],
				widgets: ['staticRow']
			});
	});
</script>
<?php include 'footer.php'; ?>

