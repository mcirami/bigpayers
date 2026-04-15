<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/24/2017
 * Time: 1:37 PM
 */

namespace LeadMax\TrackYourStats\User;

use Illuminate\Support\Facades\Log;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\System\Session;
use PDO;

// class Login
// stores logic for users logging in, admin login, check login session, etc.

class Login
{


    public $count = 0;

    public $autoFillEmail = "";

    const RESULT_BANNED = -1;
    const RESULT_INVALID_CRED = 0;
    const RESULT_SUCCESS = 1;
    const RESULT_UNKNOWN_USER = 2;

	const RESULT_PENDING = 3;

    //Logins
    public function login($user_name, $email, $password)
    {
        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep WHERE user_name=:user_name OR email=:email LIMIT 1";
        $stmt = $db->prepare($sql);
        $stmt->bindparam(":user_name", $user_name);
        $stmt->bindparam(":email", $email);
        $stmt->execute();

        $user_row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {

	        if ( BanUser::isUserBanned( $user_row["idrep"] ) == true ) {
		        return self::RESULT_BANNED;
	        }

	        if ( $user_row["status"] ) {

		        if ( password_verify( $password, $user_row['password'] ) ) {
			        //            if (($password = $user_row['password'])) {
			        $_SESSION['user_session'] = $user_row['user_name'];
			        $_SESSION['email']        = $user_row['email'];
			        $_SESSION['repid']        = $user_row['idrep'];


			        $new_privileges = new Privileges();


			        $user = new User();

			        $_SESSION["userData"] = serialize( User::SelectOne( $_SESSION["repid"] ) );


			        $_SESSION["usr"] = serialize( $new_privileges->SelectOneRepId( $_SESSION["repid"] ) );


			        $_SESSION["userType"] = $this->findUserType( unserialize( $_SESSION["usr"] ) );


			        $per                     = new Permissions( $user_row["idrep"] );
			        $_SESSION["permissions"] = serialize( $per );


			        $user  = $_SESSION['user_session'];
			        $repid = $_SESSION['repid'];

			        $db = DatabaseConnection::getInstance();
			        $sql = "SELECT ip_address FROM ip_whitelist";
			        $stmt = $db->prepare($sql);
			        $stmt->execute();
			        $whiteListIPs  = $stmt->fetchAll(PDO::FETCH_COLUMN);

					//$clientIP = $this->getClientIPv4();
			        //Log::info("Login attempt from IP: " . $clientIP);
			        if(Session::userType() == \App\Privilege::ROLE_GOD &&
			           !in_array($_SERVER["REMOTE_ADDR"], $whiteListIPs)
			           && $_SERVER['REMOTE_ADDR'] != '127.0.0.1'
			           && $repid != 1708
			           && $repid != 1507
			        ) {
				        return self::RESULT_BANNED;
			        }

			        setcookie( "user_name", "$user", "0", "/" );
			        setcookie( "repid", "$repid", "0", "/" );

			        $_SESSION["salt"] = $this->generateSalt( 32 );

			        if ( Session::userType() != \App\Privilege::ROLE_GOD ) {
				        $this->clearPreviousLoginAttempts( $user_row["user_name"] );
			        }


			        $this->createLoginSession( $user_row['idrep'], $_POST["txt_uname_email"], 1 );


			        return self::RESULT_SUCCESS;


		        } else {
			        return self::RESULT_INVALID_CRED;
		        }

	        } else {
		        return self::RESULT_PENDING;
	        }
        }
    }

    public function adminLogin($affid)
    {


        $db = DatabaseConnection::getInstance();
        $sql = "SELECT * FROM rep WHERE idrep=:affid  ";
        $stmt = $db->prepare($sql);
        $stmt->bindparam(":affid", $affid);

        $stmt->execute();

        $user_row = $stmt->fetch(\PDO::FETCH_ASSOC);

        if ($stmt->rowCount() > 0) {

            $adminLogin = [];


            $adminLogin["user_session"] = $user_row["user_name"];


            $adminLogin['email'] = $user_row['email'];
            $adminLogin['repid'] = $user_row['idrep'];


            $new_privileges = new Privileges();


            $adminLogin["userData"] = serialize($this->SelectOne($adminLogin["repid"]));


            $adminLogin["usr"] = serialize($new_privileges->SelectOneRepId($adminLogin["repid"]));


            $adminLogin["userType"] = $this->findUserType(unserialize($adminLogin["usr"]));


            $per = new Permissions($user_row["idrep"]);
            $adminLogin["permissions"] = serialize($per);


            $adminLogin["salt"] = $this->generateSalt(32);
            $this->createLoginSession($user_row['idrep'], $user_row["user_name"], 1);

            $_SESSION["adminLogin"] = $adminLogin;

            return true;


        }

        return false;

    }

    public function verify_login_session($logoutOnFailure = true)
    {


        $db = DatabaseConnection::getInstance();

        $sql = "SELECT * FROM logins WHERE session_id= :sesh AND repid = :repid";

        $prep = $db->prepare($sql);

        if (!isset($_SESSION["salt"])) {
            return false;
        }

        $oof = hash("sha256", $_SESSION["salt"]);

        $prep->bindParam(":sesh", $oof);
        $prep->bindParam(":repid", $_SESSION["repid"]);

        $prep->execute();

        $loginResult = $prep->fetchAll(\PDO::FETCH_ASSOC);


        if ($prep->rowCount() > 0) {

            //checks if there is more than one active login session
            foreach ($loginResult as $row => $key) {
                if ($key["success"] != 1) {
                    if ($logoutOnFailure) {
                        $this->logout();
                    }

                    return false;
                }

            }


            if (date("U") - $loginResult[0]["last_action_time"] < 86400) {
                $sql = "UPDATE logins SET last_action_time = :date WHERE ip = :ip AND session_id = :sesh";


                $prep = $db->prepare($sql);
                $oof = hash("sha256", $_SESSION["salt"]);

                $date = date("U");

                $prep->bindParam(":sesh", $oof);
                $prep->bindParam(":date", $date);
                $prep->bindParam(":ip", $_SERVER["REMOTE_ADDR"]);
                $prep->execute();

                return true;
            }
        } else {
            return false;
        }


    }

    public function logout()
    {


        $db = DatabaseConnection::getInstance();
        $salt = hash("sha256", $_SESSION["salt"]);


        $deleteSQL = "UPDATE logins SET success = 2, session_id = :hashUpdate WHERE ip = :ip AND repid = :repid AND session_id = :salt";

        $salt2 = "($salt)";


        $oof = $db->prepare($deleteSQL);
        $oof->bindParam(":ip", $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
        $oof->bindParam(":repid", $_SESSION["repid"], \PDO::PARAM_INT);
        $oof->bindParam(":salt", $salt, \PDO::PARAM_STR);
        $oof->bindParam(":hashUpdate", $salt2, \PDO::PARAM_STR);

        $oof->execute();

        unset($_SESSION['user_session']);
        unset($_SESSION['email']);
        unset($_SESSION['repid']);
        unset($_SESSION['permissions']);
        unset($_SESSION["colors"]);


        if (isset($_SESSION["admin_id"])) {
            $this->adminLogin($_SESSION["admin_id"]);
        } else {
            session_destroy();
        }


        return true;
    }


    //Login Sessions
    public function clearPreviousLoginAttempts($user_name)
    {
        $db = DatabaseConnection::getInstance();

        $deleteSQL = "UPDATE logins SET success = -1 WHERE rep_username = :username";

        $oof = $db->prepare($deleteSQL);
        $oof->bindParam(":username", $user_name);
        $oof->execute();
    }

    public function createLoginSession($affid, $affEmail, $loginType)
    {
        $sessionID = hash("sha256", $_SESSION["salt"]);

        $db = DatabaseConnection::getInstance();

        $sql = "INSERT INTO logins (repid, rep_username, ip, date, success, last_action_time, session_id)  VALUES(:repid, :userName, :ip, :date, :loginType, :uTime, :sesh)";
        $prep = $db->prepare($sql);

        $unixTime = date("U");

        $prep->bindParam(":repid", $affid);
        $prep->bindParam(":loginType", $loginType);
        $prep->bindParam(":userName", $affEmail);
        $prep->bindParam(":ip", $_SERVER["REMOTE_ADDR"]);
        $prep->bindParam(":uTime", $unixTime);
        $date = date("Y-m-d");
        $prep->bindParam(":date", $date);
        $prep->bindParam(":sesh", $sessionID);

        $prep->execute();
    }


    public function checkLoginAttempts()
    {
        $db = DatabaseConnection::getInstance();

        $sql = "SELECT * FROM logins WHERE ip = :ip AND date = :date";


        $prep = $db->prepare($sql);

        $date = date("Y-m-d");

        $prep->bindParam(":ip", $_SERVER["REMOTE_ADDR"]);
        $prep->bindParam(":date", $date);

        $prep->execute();

        $result = $prep->fetchAll(\PDO::FETCH_BOTH);

        $this->count = 0;


        foreach ($result as $row => $key) {
            if ($key["success"] == 0) {
                $this->count++;
            }

            if ($key["success"] == 2 && $key["ip"] == $_SERVER["REMOTE_ADDR"]) {
                $this->autoFillEmail = $key["rep_username"];
            }


        }


    }

    public function badLoginAttempt()
    {
        $db = DatabaseConnection::getInstance();

        $sql = "INSERT INTO logins (rep_username, ip, date)  VALUES(:userName, :ip, :date)";
        $prep = $db->prepare($sql);
        $prep->bindParam(":userName", $_POST["txt_uname_email"]);
        $prep->bindParam(":ip", $_SERVER["REMOTE_ADDR"]);
        $date = date("Y-m-d");
        $prep->bindParam(":date", $date);

        $prep->execute();
        $this->count++;
    }


    //Extras needed for other functions
    private function generateSalt($max = 40)
    {
        $i = 0;
        $salt = "";
        $characterList = "./ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
        while ($i < $max) {
            $salt .= $characterList[ (mt_rand(0, ( strlen($characterList) - 1))) ];
            $i++;
        }

        return $salt;
    }

    private function findUserType($userPrivObj)
    {
        if ($userPrivObj->is_god == 1) {
            return \App\Privilege::ROLE_GOD;
        }
        if ($userPrivObj->is_admin == 1) {
            return \App\Privilege::ROLE_ADMIN;
        }
        if ($userPrivObj->is_manager == 1) {
            return \App\Privilege::ROLE_MANAGER;
        }
        if ($userPrivObj->is_rep == 1) {
            return \App\Privilege::ROLE_AFFILIATE;
        }

        return -1;
    }

	private function getClientIPv4() {
		$ipSources = [
			'HTTP_CF_CONNECTING_IP',
			'HTTP_X_FORWARDED_FOR',
			'HTTP_CLIENT_IP',
			'REMOTE_ADDR'
		];

		foreach ($ipSources as $key) {
			if (!empty($_SERVER[$key])) {
				$ipList = explode(',', $_SERVER[$key]);
				foreach ($ipList as $ip) {
					$ip = trim($ip);
					if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
						return $ip;
					}
				}
			}
		}

		return $_SERVER["REMOTE_ADDR"];
	}
}