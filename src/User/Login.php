<?php
/**
 * Created by PhpStorm.
 * User: dean
 * Date: 7/24/2017
 * Time: 1:37 PM
 */

namespace LeadMax\TrackYourStats\User;

use Illuminate\Support\Facades\Log;
use App\Support\CurrentUserSession;
use App\Support\LegacyDatabaseConnection as DatabaseConnection;
use App\Support\NativeSession;
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
			        NativeSession::put('user_session', $user_row['user_name']);
			        NativeSession::put('email', $user_row['email']);
			        NativeSession::put('repid', $user_row['idrep']);


			        $new_privileges = new Privileges();


			        $user = new User();
                    $repid = NativeSession::get('repid');

			        NativeSession::put('userData', serialize(User::SelectOne($repid)));


                    $userPrivileges = $new_privileges->SelectOneRepId($repid);
			        NativeSession::put('usr', serialize($userPrivileges));


			        NativeSession::put('userType', $this->findUserType($userPrivileges));


			        $per                     = new Permissions( $user_row["idrep"] );
			        NativeSession::put('permissions', serialize($per));


			        $user  = NativeSession::get('user_session');
			        $repid = NativeSession::get('repid');

			        $db = DatabaseConnection::getInstance();
			        $sql = "SELECT ip_address FROM ip_whitelist";
			        $stmt = $db->prepare($sql);
			        $stmt->execute();
			        $whiteListIPs  = $stmt->fetchAll(PDO::FETCH_COLUMN);

					//$clientIP = $this->getClientIPv4();
			        //Log::info("Login attempt from IP: " . $clientIP);
			        if(CurrentUserSession::type() == \App\Privilege::ROLE_GOD &&
			           !in_array($_SERVER["REMOTE_ADDR"], $whiteListIPs)
			           && $_SERVER['REMOTE_ADDR'] != '127.0.0.1'
			           && $repid != 1708
			           && $repid != 1507
			        ) {
				        return self::RESULT_BANNED;
			        }

			        setcookie( "user_name", "$user", "0", "/" );
			        setcookie( "repid", "$repid", "0", "/" );

			        NativeSession::put('salt', $this->generateSalt( 32 ));

			        if ( CurrentUserSession::type() != \App\Privilege::ROLE_GOD ) {
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

            NativeSession::put('adminLogin', $adminLogin);

            return true;


        }

        return false;

    }

    public function verify_login_session($logoutOnFailure = true)
    {


        $db = DatabaseConnection::getInstance();

        $sql = "SELECT * FROM logins WHERE session_id= :sesh AND repid = :repid";

        $prep = $db->prepare($sql);

        $salt = NativeSession::get('salt');

        if ($salt === null) {
            return false;
        }

        $oof = hash("sha256", $salt);
        $repid = NativeSession::get('repid');

        $prep->bindParam(":sesh", $oof);
        $prep->bindParam(":repid", $repid);

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
                $oof = hash("sha256", $salt);

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
        $salt = hash("sha256", NativeSession::get('salt'));
        $repid = NativeSession::get('repid');


        $deleteSQL = "UPDATE logins SET success = 2, session_id = :hashUpdate WHERE ip = :ip AND repid = :repid AND session_id = :salt";

        $salt2 = "($salt)";


        $oof = $db->prepare($deleteSQL);
        $oof->bindParam(":ip", $_SERVER["REMOTE_ADDR"], \PDO::PARAM_STR);
        $oof->bindParam(":repid", $repid, \PDO::PARAM_INT);
        $oof->bindParam(":salt", $salt, \PDO::PARAM_STR);
        $oof->bindParam(":hashUpdate", $salt2, \PDO::PARAM_STR);

        $oof->execute();

        NativeSession::forget('user_session');
        NativeSession::forget('email');
        NativeSession::forget('repid');
        NativeSession::forget('permissions');
        NativeSession::forget('colors');


        $adminId = NativeSession::get('admin_id');

        if ($adminId !== null) {
            $this->adminLogin($adminId);
        } else {
            NativeSession::destroy();
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
        $sessionID = hash("sha256", NativeSession::get('salt'));

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
