</div> <!-- close panels_wrap from header -->

<script type = "text/javascript" src = "js/dropdown.min.js"></script>
<script type = "text/javascript" src = "js/jquery-ui.min.js"></script>
<script type = "text/javascript" src = "js/bootstrap.min.js"></script>
<script type = "text/javascript" src = "js/bootstrap-tooltip.js"></script>
<script type = "text/javascript" src = "js/jquery.tablesorter.min.js"></script>
<script type = "text/javascript" src = "js/widget-staticRow.min.js"></script>
<script type = "text/javascript" src = "js/moment-timezone-with-data.js"></script>
<script type = "text/javascript" src = "js/jquery-ui-timepicker-addon.js"></script>

<script type = "text/javascript" src = "js/drawer.min.js" charset = "utf-8"></script>
<script type = "text/javascript">
	$(document).ready(function () {
		$('.drawer').drawer();
		$('[data-toggle="popover"]').popover();
	});


</script>


<?php
$adminLogin = new \LeadMax\TrackYourStats\User\AdminLogin();
$adminLogin->appendJavascript();
?>

</body>
</html>

