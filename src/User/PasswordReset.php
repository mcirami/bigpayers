<?php


use LeadMax\TrackYourStats\System\Mail;

// all business logic for password resets


function checkPasswordResetRequest()
{
    if (isset($_POST["email"])) {

        if (filter_var($_POST["email"], FILTER_VALIDATE_EMAIL)) {
            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

            $sql = "SELECT first_name, email, idrep, user_name FROM rep where email = :email";


            $prep = $db->prepare($sql);

            $prep->bindParam(":email", $_POST["email"]);
            $prep->execute();
            $result = $prep->fetch(PDO::FETCH_ASSOC);


            if ($prep->rowCount() > 0) {
                $SQL = "INSERT INTO password_resets (repid, user_name, email, verify, time_stamp, ip, active) VALUES(:repid, :user_name, :email, :verify, :time_stamp, :ip, 1)";

                $OOF = $db->prepare($SQL);

                $date = date("U");


                $salt = salt("40");

                $hash = hash("sha512", $salt);


                $OOF->bindParam(":repid", $result["idrep"]);
                $OOF->bindParam(":user_name", $result["user_name"]);
                $OOF->bindParam(":email", $result["email"]);
                $OOF->bindParam(":verify", $hash);
                $OOF->bindParam(":time_stamp", $date);
                $OOF->bindParam(":ip", $_SERVER["REMOTE_ADDR"]);

                $OOF->execute();


                $webroot = getWebRoot();

                $message =
                    "<html>
                            <body>
                                <p>Greetings {$result["first_name"]},</p><p>A password reset has been requested today ({$date}) from {$_SERVER["REMOTE_ADDR"]}</p>
                            <br/>
                                     
                            <p>You can reset your password with this link:
                              <a href=\"{$webroot}aff_help.php?token={$hash}\">Here</a>
                            </p>

                            <p>
                                If you did not request this, then ignore and it will expire in one day. 
                                If you would like to report abuse, please contact the webmaster at TrackYourStats. </p><p>DO NOT reply to this email, this is automated and you will not receive a response.
                            </p>
                            <br/>
                            <p>
                                Thank you and have a great day,
                                <br/>
                                Devs @ TrackaYouStats.
                            </p>
                            </body>
                        </html>";


                $mailer = new Mail($result["email"], "Password Reset - TrackYourStats", $message);
                $mailer->send();


            }
        } else {
            $notEmail = false;
        }

        global $autoFill;

        if (isset($notEmail)) {
            $autoFill = "Invalid email.";
        } else {
            $autoFill = "If that email was associated with a user they have been emailed.<br>Allow 5 minutes for email to send, usually imediately.";
        }


    }

}

function checkToken()
{
    global $token;
    global $HAOOF;

    if (isset($_GET["token"])) {

        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $prep = $db->prepare("SELECT * FROM password_resets WHERE verify = :token AND active = 1");
        $prep->bindParam(":token", $_GET["token"]);
        $prep->execute();

        $result = $prep->fetch(PDO::FETCH_ASSOC);

        if ($prep->rowCount() > 0) {

            $token = $_GET["token"];
            $HAOOF = " for {$result["user_name"]},";

        }


    }
}


function checkPasswordAndReset()
{
    global $autoFill;
    global $token;

    if (isset($_POST["password"]) && isset($_POST["confirmpassword"]) && isset($_POST["token"])) {
        if ($_POST["password"] == $_POST["confirmpassword"]) {
            $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

            $prep = $db->prepare("SELECT * FROM password_resets WHERE verify = :token AND active = 1");
            $prep->bindParam(":token", $_POST["token"]);
            $prep->execute();

            $result = $prep->fetch(PDO::FETCH_ASSOC);

            $date = date("U");

            if ($result && ($date - $result["time_stamp"]) < 86400) //if it has been less than a day since password reset
            {

                $hash = password_hash($_POST["password"], PASSWORD_DEFAULT);
                $prep = $db->prepare("UPDATE rep SET password = :hash WHERE idrep = :idrep");

                $prep->bindParam(":idrep", $result["repid"]);
                $prep->bindParam(":hash", $hash);
                $prep->execute();


                $prep = $db->prepare("UPDATE password_resets SET active = 0 WHERE verify = :token");
                $prep->bindParam(":token", $_POST["token"]);
                $prep->execute();

                $autoFill = "Password successfully reset for {$result["user_name"]}. <a href='/login.php'>Go to login.</a>";

            } else {
                $autoFill = "Token has expired, please request a new reset.";
            }
        } else {
            $autoFill = "Passwords don't match.";
            $token = $_POST["token"];
        }


    }
}