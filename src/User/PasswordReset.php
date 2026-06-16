<?php


use App\Support\LegacyDatabaseConnection as DatabaseConnection;
use App\Support\LegacyMail as Mail;
use App\Support\NativeRequest;

// all business logic for password resets


function checkPasswordResetRequest()
{
    $email = NativeRequest::post('email');

    if ($email !== null) {

        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $db = DatabaseConnection::getInstance();

            $sql = "SELECT first_name, email, idrep, user_name FROM rep where email = :email";


            $prep = $db->prepare($sql);

            $prep->bindParam(":email", $email);
            $prep->execute();
            $result = $prep->fetch(PDO::FETCH_ASSOC);


            if ($prep->rowCount() > 0) {
                $SQL = "INSERT INTO password_resets (repid, user_name, email, verify, time_stamp, ip, active) VALUES(:repid, :user_name, :email, :verify, :time_stamp, :ip, 1)";

                $OOF = $db->prepare($SQL);

                $date = date("U");
                $remoteAddress = NativeRequest::server('REMOTE_ADDR');


                $salt = salt("40");

                $hash = hash("sha512", $salt);


                $OOF->bindParam(":repid", $result["idrep"]);
                $OOF->bindParam(":user_name", $result["user_name"]);
                $OOF->bindParam(":email", $result["email"]);
                $OOF->bindParam(":verify", $hash);
                $OOF->bindParam(":time_stamp", $date);
                $OOF->bindParam(":ip", $remoteAddress);

                $OOF->execute();


                $webroot = getWebRoot();

                $message =
                    "<html>
                            <body>
                                <p>Greetings {$result["first_name"]},</p><p>A password reset has been requested today ({$date}) from {$remoteAddress}</p>
                            <br/>
                                     
                            <p>You can reset your password with this link:
                              <a href=\"" . url('/forgot-password?token=' . $hash) . "\">Here</a>
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

    $requestToken = NativeRequest::query('token');

    if ($requestToken !== null) {

        $db = DatabaseConnection::getInstance();

        $prep = $db->prepare("SELECT * FROM password_resets WHERE verify = :token AND active = 1");
        $prep->bindParam(":token", $requestToken);
        $prep->execute();

        $result = $prep->fetch(PDO::FETCH_ASSOC);

        if ($prep->rowCount() > 0) {

            $token = $requestToken;
            $HAOOF = " for {$result["user_name"]},";

        }


    }
}


function checkPasswordAndReset()
{
    global $autoFill;
    global $token;

    $password = NativeRequest::post('password');
    $confirmPassword = NativeRequest::post('confirmpassword');
    $requestToken = NativeRequest::post('token');

    if ($password !== null && $confirmPassword !== null && $requestToken !== null) {
        if ($password == $confirmPassword) {
            $db = DatabaseConnection::getInstance();

            $prep = $db->prepare("SELECT * FROM password_resets WHERE verify = :token AND active = 1");
            $prep->bindParam(":token", $requestToken);
            $prep->execute();

            $result = $prep->fetch(PDO::FETCH_ASSOC);

            $date = date("U");

            if ($result && ($date - $result["time_stamp"]) < 86400) //if it has been less than a day since password reset
            {

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $prep = $db->prepare("UPDATE rep SET password = :hash WHERE idrep = :idrep");

                $prep->bindParam(":idrep", $result["repid"]);
                $prep->bindParam(":hash", $hash);
                $prep->execute();


                $prep = $db->prepare("UPDATE password_resets SET active = 0 WHERE verify = :token");
                $prep->bindParam(":token", $requestToken);
                $prep->execute();

                $autoFill = "Password successfully reset for {$result["user_name"]}. <a href='/login'>Go to login.</a>";

            } else {
                $autoFill = "Token has expired, please request a new reset.";
            }
        } else {
            $autoFill = "Passwords don't match.";
            $token = $requestToken;
        }


    }
}
