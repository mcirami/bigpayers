<?php

namespace LeadMax\TrackYourStats\System;

use App\Services\BaseInstallSql;
use App\Services\TenantDatabasePdoFactory;
use App\Support\LegacyDatabaseConnection as DatabaseConnection;
use App\Support\NativeRequest;

// Class used when setting up new company installs

// has function to create a new company in the company table in the master DB,
// create a new database for the company,
// and create an admin for the new install

// still need to have a function to insert db dump to new db

class Setup
{
    private $baseInstallSql;
    private $databases;

    public function __construct(?BaseInstallSql $baseInstallSql = null, ?TenantDatabasePdoFactory $databases = null)
    {
        $this->baseInstallSql = $baseInstallSql ?: new BaseInstallSql();
        $this->databases = $databases ?: new TenantDatabasePdoFactory();
    }


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
            $tenant = $this->databases->make($this->subDomain());
            $tenant->exec($this->baseInstallSql->contents());

            return true;
        } catch (\Exception $e) {
            return $e;
        }
    }

    function createDatabase()
    {
        try {
            $db = $this->databases->make();
            $db->exec('CREATE SCHEMA IF NOT EXISTS ' . $this->databases->quoteIdentifier($this->subDomain()));

            return true;
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
            $db = DatabaseConnection::getInstance();
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
                        $msg = "<html><body><h1>A company was setup from ".NativeRequest::server('REMOTE_ADDR')."</h1><br/>";
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

    private function subDomain(): string
    {
        return strtolower(trim((string) post("subDomain")));
    }
}
