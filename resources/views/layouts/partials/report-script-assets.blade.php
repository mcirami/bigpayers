<script type="text/javascript" src="{{ $webroot }}js/moment.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/jquery_2.1.3_jquery.min.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/jquery-ui.min.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/jscolor.min.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/main.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/tables.js?v=1.1"></script>
<script type="text/javascript" src="{{ $webroot }}js/bootstrap.min.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/bootstrap-tooltip.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/bootstrap-notify.min.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/jquery.tablesorter2.31.3.min.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/jquery.tablesorter.pager.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/widget-staticRow.min.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/moment-timezone-with-data.js"></script>
<script type="text/javascript" src="{{ $webroot }}js/jquery-ui-timepicker-addon.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        $('[data-toggle="popover"]').popover();
    });
</script>

@php
    $adminLogin = new \LeadMax\TrackYourStats\User\AdminLogin();
    $adminLogin->appendJavascript();
@endphp
