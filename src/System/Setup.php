<?php

namespace LeadMax\TrackYourStats\System;

use PDO;

// Class used when setting up new company installs

// has function to create a new company in the company table in the master DB,
// create a new database for the company,
// and create an admin for the new install

// still need to have a function to insert db dump to new db

class Setup
{


    function createAdmin()
    {
        if (post("submit")) {
            if (post("password") != post("confirmPassword")) {
                return "BAD_PWD";
            }

            $email = post("adminEmail");
            $userName = post("userName");
            $password = post("password");

        }
    }

    public function installDB()
    {
        try {
            $db = new PDO("mysql:host=localhost;", "tys_create_db", "DWcuvaXOq9KdK9dM");

            require "resources/importDB.php";


            $db = $db->prepare("USE ".post("subDomain").";".$query);
            if ($db->execute()) {
                return true;
            }

            return false;


        } catch (\Exception $e) {
            return $e;
        }
    }

    function createDatabase()
    {
        try {
            $db = new PDO("mysql:host=localhost;", "tys_create_db", "DWcuvaXOq9KdK9dM");


            $db = $db->prepare("CREATE SCHEMA IF NOT EXISTS ".post("subDomain"));
            if ($db->execute()) {
                return true;
            }

            return false;


        } catch (\Exception $e) {
            return $e;
        }

    }

    function setup()
    {
        if (post("submit")) {
            $shortHand = post("shortHand");
            $subDomain = post("subDomain");
            $companyName = post("companyName");
            $address = post("address");
            $city = post("city");
            $state = post("state");
            $zip = post("zip");
            $telephone = post("telephone");
            $email = post("email");
            $skype = post("skype");

            $pwd = salt(12);

            echo "SUBMIT";
            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
            $sql = "INSERT INTO company (shortHand, subDomain, companyName, address, city, state, zip, telephone, email, skype, colors, uid) 
              VALUES(:shortHand, :subDomain, :companyName, :address, :city, :state, :zip, :telephone, :email, :skype, :colors, :uid);";
            $prep = $db->prepare($sql);
            $prep->bindParam(":shortHand", $shortHand);
            $prep->bindParam(":subDomain", $subDomain);
            $prep->bindParam(":companyName", $companyName);
            $prep->bindParam(":address", $address);
            $prep->bindParam(":city", $city);
            $prep->bindParam(":state", $state);
            $prep->bindParam(":zip", $zip);
            $prep->bindParam(":telephone", $telephone);
            $prep->bindParam(":email", $email);
            $prep->bindParam(":skype", $skype);
            $colors = "484848;FFFFFF;2A58AD;1D4C9E;82A7EB;FCED16;EAEEF1;FFFFFF;404452;999999";
            $prep->bindParam(":colors", $colors);
            $prep->bindParam(":uid", salt(4, true));


            if ($prep->execute()) {

                if ($this->createDatabase()) {
                    if ($this->installDB()) {
                        $msg = "<html><body><h1>A company was setup from ".$_SERVER["REMOTE_ADDR"]."</h1><br/>";
                        $msg .= "<p>company Short Hand: {$shortHand} </p>";
                        $msg .= "<p>Sub Domain: {$subDomain} </p>";
                        $msg .= "<br/><h2>company Contact:</h2>";
                        $msg .= "<p>Full company Name: {$companyName}</p>";
                        $msg .= "<p>Address: {$address}</p>";
                        $msg .= "<p>City: {$city}</p>";
                        $msg .= "<p>State: {$state}</p>";
                        $msg .= "<p>Zip: {$zip}</p>";
                        $msg .= "<p>Telephone: {$telephone}</p>";
                        $msg .= "<p>Email: {$email}</p>";
                        $msg .= "<p>Skype: {$skype}\</p><br/><br/>";

                        $msg .= "<h2>Admin Account: </h2>";
                        $adminEmail = post("adminEmail");
                        $userName = post("userName");
                        $password = post("password");

                        $msg .= "<p>Email: {$adminEmail}</p>";
                        $msg .= "<p>Username: {$userName}</p>";
                        $msg .= "<p>Password: {$password}</p></body></html>";


                        $mail = new Mail("dwm348@gmail.com", "New company Install - ".$shortHand, $msg);
                        echo $mail->send();

                        return "SUCCESS";
                    }


                }


            } else {
                return "FAILED";
            }

        }

        return "NO POST";

    }
}