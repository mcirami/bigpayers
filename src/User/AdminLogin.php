<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 11/2/2017
 * Time: 1:52 PM
 */

namespace LeadMax\TrackYourStats\User;


class AdminLogin
{

    public $isSet = false;

    function __construct()
    {
        if (isset($_GET["adminLogin"]) && isset($_SESSION["adminLogin"])) {
            $this->isSet = true;
        }
    }

    public function appendJavascript()
    {
        if ($this->isSet) {
            echo "
<script type='text/javascript'>



$('a').each(function() {
    var href = $(this).attr('href');

    if (href) {
        href += (href.match(/\?/) ? '&' : '?') + 'adminLogin';
        $(this).attr('href', href);
    }
});

$('form').each(function (){
   var action = $(this).attr('action');
        action  += (action.match(/\?/) ? '&' : '?') + 'adminLogin';
   $(this).attr('action', action);
});

var currentOnClick = $('#searchBtn').attr('onclick');
$('#searchBtn').attr('onclick', currentOnClick + ' + \'&adminLogin\'');
           </script>
         
         ";
        }


    }


}