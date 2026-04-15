<?php namespace LeadMax\TrackYourStats\System;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/6/2017
 * Time: 1:56 PM
 */
class Notify
{

    public static function info($title, $message, $wait = false)
    {
        echo "<script type='text/javascript'>
              $.notify({

                title:  '{$title}',
                message: '{$message}'
                
            }, {
            placement: {
                from: 'top',
                align: 'center'
            },
                type: 'info',
            animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            },
            }
        );
            

</script>";

        if ($wait) {
            sleep($wait);
        }
    }

    public static function error($title, $message, $wait = false)
    {
        echo "<script type='text/javascript'>
              $.notify({

                title:  '{$title}',
                message: '{$message}'
                
            }, {
            placement: {
                from: 'top',
                align: 'center'
            },
                type: 'danger',
            animate: {
                enter: 'animated fadeInDown',
                exit: 'animated fadeOutUp'
            },
            }
        );
            

</script>";

        if ($wait) {
            sleep($wait);
        }
    }

}