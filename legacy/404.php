<?php


$webroot = getWebRoot();




?>


<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" type="text/css" href="<?php echo $webroot; ?>css/default.css"/>
    <link rel="stylesheet" media="screen" type="text/css"
          href="<?php echo $webroot; ?>css/company.php?>"/>
    <link href="<?php echo $webroot; ?>css/responsive_table.css" rel="stylesheet" type="text/css"/>
    <link href="<?php echo $webroot; ?>css/drawer.min.css" rel="stylesheet">

    <style>
        .right_panel{
            width:100%;
        }

        .not_found_page {
            width: 100%;
            float: left;
            text-align: center;
            height: 100%;
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: horizontal;
            -webkit-box-direction: normal;
            -ms-flex-direction: row;
            flex-direction: row;
            -ms-flex-wrap: nowrap;
            flex-wrap: nowrap;
            align-items: center;
        }

        .not_found_page .content_wrap {
            display: -webkit-box;
            display: -ms-flexbox;
            display: flex;
            -webkit-box-orient: vertical;
            -webkit-box-direction: normal;
            -ms-flex-direction: column;
            flex-direction: column;
            -ms-flex-wrap: nowrap;
            flex-wrap: nowrap;
            width: 100%;
            padding: 0 20px 15%;
        }

        .not_found_page h2 {
            font-size: 34px;
        }

        .not_found_page p {
            font-size: 18px;
            margin-top: 40px;
        }

        @media all and (max-width: 1023px) {

            ￼.not_found_page h2 {
                font-size: 28px;
            }

            .not_found_page p {
                font-size: 16px;
                margin-top: 30px;
            }
        }

        @media all and (max-width: 768px) {

            .not_found_page .content_wrap {
                padding: 0 20px 30%;
            }

            .not_found_page h2 {
                font-size: 24px;
            }

            .not_found_page p {
                font-size: 14px;
                margin-top: 20px;
            }

        }

    </style>

    <title>Not Good!</title>
</head>
<body style="background-color:#EAEEF1;">

<div class="right_panel">

    <section class="not_found_page">
        <div class="content_wrap">
            <h2>404 Error.</h2>
            <p>Whatever you're after, sure aint here!</p>
        </div>


    </section>

</div>
</body>
</html>
