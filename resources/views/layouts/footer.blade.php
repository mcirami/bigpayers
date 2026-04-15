<script type="text/javascript" src="{{$webroot}}js/dropdown.min.js"></script>
<script type="text/javascript" src="{{$webroot}}js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{{$webroot}}js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{$webroot}}js/bootstrap-tooltip.js"></script>
{{--<script type="text/javascript" src="{{$webroot}}js/jquery.tablesorter.min.js"></script>--}}
<script type="text/javascript" src="{{$webroot}}js/jquery.tablesorter2.31.3.min.js"></script>
<script type="text/javascript" src="{{$webroot}}js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="{{$webroot}}js/widget-staticRow.min.js"></script>
<script type="text/javascript" src="{{$webroot}}js/moment-timezone-with-data.js"></script>
<script type="text/javascript" src="{{$webroot}}js/jquery-ui-timepicker-addon.js"></script>

<script type="text/javascript" src="{{$webroot}}js/drawer.min.js" charset="utf-8"></script>
<script type="text/javascript">
  $(document).ready(function() {
    $('.drawer').drawer();
    $('[data-toggle="popover"]').popover();
  });


</script>


{{--<script type="text/javascript" src="/js/app.js"></script>--}}


@php
    $adminLogin = new \LeadMax\TrackYourStats\User\AdminLogin();
    $adminLogin->appendJavascript();
@endphp

