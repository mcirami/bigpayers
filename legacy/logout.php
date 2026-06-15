<?php

use App\Support\NativeSession;
use App\Support\NativeRequest;
use LeadMax\TrackYourStats\User\User;

if(NativeRequest::hasQuery('adminLogin'))
{
    NativeSession::forget('adminLogin');
?>
<script type="text/javascript">
    window.close();
    </script>

<?php
exit;
}


$user_logout = new User();

$user_logout->logout();
send_to('login');
