<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/14/2017
 * Time: 11:54 AM
 */

try {

    require "app/System/AutoLoad.php";




    System\loadIncludes();


    $installer = new System\Setup();

    echo $installer->setup();

}catch(Exception $e)
{
    die($e);
}



?>

<!DOCTYPE HTML>
<html>
<head>
    <title>TrackYourStats Setup</title>
    <style>
        .seperateBox {
            border-style: ridge;
            width: 45%;
            height: auto;
            padding: 10px;

        }

        .divEntry {
            margin-top: 10px;
            padding: 5px;
        }
    </style>
</head>

<body>
<form action="<?PHP echo parse_url($_SERVER["REQUEST_URI"])["path"]; ?>" method="post">

    <u>Company Information</u>
    <div class="seperateBox">
        <div class="divEntry">
            <label>Company Short Hand:</label>
            <input required type="text" name="shortHand" placeholder="Track Your Stats"><br/>
            <small>This will appear as the tab title in browser.</small>

        </div>
        <div class="divEntry">

            <label>Sub Domain:</label>
            <input required type="text" name="subDomain" placeholder="tys"><br/>
            <small>A subdomain of abc would appear as "abc.trackyourstats.com"</small>
            <br/>
        </div>
        <div class="divEntry">

            <label>Company Contact</label>
            <div class="seperateBox">
                <div class="divEntry">

                    <label>Full Company Name:</label>
                    <input required type="text" name="companyName" placeholder="Track Your Stats"><br/>
                </div>

                <div class="divEntry">

                    <label>Address:</label>
                    <input required type="text" name="address" placeholder="123 Company Lane"><br/>
                </div>
                <div class="divEntry">

                    <label>City:</label>
                    <input required type="text" name="city" placeholder="Boston"><br/>
                </div>
                <div class="divEntry">

                    <label>State:</label>
                    <input required type="text" name="state" placeholder="New York"><br/>
                </div>
                <div class="divEntry">

                    <label>Zip Code:</label>
                    <input required type="text" name="zip" placeholder="123456"><br/>
                </div>
                <div class="divEntry">

                    <label>Telephone:</label>
                    <input required type="text" name="telephone" placeholder="555-555-5555"><br/>
                </div>
                <div class="divEntry">

                    <label>Email:</label>
                    <input required type="text" name="email" placeholder="admin@admin.com"><br/>
                </div>
                <div class="divEntry">

                    <label>Skype:</label>
                    <input type="text" name="skype" placeholder="company.name"><br/>
                </div>
            </div>

        </div>

        <div class="divEntry">

            <u>Create an Admin</u>
            <div class="divEntry">
                <label>Email:</label>
                <input type="text" name="admimEmail" placeholder=""><br/>
            </div>
            <div class="divEntry">
                <label>User name:</label>
                <input type="text" name="userName" placeholder=""><br/>
            </div>
            <div class="divEntry">
                <label>Password:</label>
                <input type="password" name="password" placeholder=""><br/>
            </div>
            <div class="divEntry">
                <label>Confirm Password:</label>
                <input type="password" name="confirmPassword" placeholder=""><br/>
            </div>
        </div>
    </div>


    <div style="padding:20px;">
        <input type="submit" value="Submit" name="submit">
    </div>

</form>

</body>


</html>
