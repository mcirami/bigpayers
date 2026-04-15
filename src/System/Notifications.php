<?php namespace LeadMax\TrackYourStats\System;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 9/21/2017
 * Time: 10:47 AM
 */

use PDO;


class Notifications
{


    public $notifications = array();

    public $userID = 0;


    private $newNotification = array();

    private $userList = array();


    public function __construct($userID)
    {
        $this->userID = $userID;

    }

    public function get($ID)
    {
        foreach ($this->notifications as $not) {
            if ($not["id"] == $ID) {
                return $not;
            }
        }
    }


    public function view($id)
    {


        $selected = $this->get($id);
        $date = \Carbon\Carbon::createFromTimestamp($selected["timestamp"])->toFormattedDateString();

        echo "<h4>{$selected["title"]} - {$date}</h4>";
        echo "<br/>";
        echo "<p>{$selected["body"]}</p>";
        echo "<br/>";

        echo "<p>Regards,</p>";
        echo "<p>{$selected["author_user_name"]}</p>";
        echo "<br/>";
        echo "
    <a class='btn btn-default btn-sm' href='notifications.php'><img src='/images/icons/arrow_turn_left.png' alt=''> &nbsp;Back</a>
 <a class='btn btn-default btn-sm' href='notifications.php?action=mark&id={$id}'><img src='/images/icons/folder_table.png' alt='Mark as Read'> &nbsp;Mark as Read</a>
                        <a class='btn btn-default btn-sm' onclick='confirmPlease({$id});' href='javascript:void(0);'><img src='/images/icons/bin.png' alt='Delete'>&nbsp; Delete</a>";


    }

    public function checkPostAndCreate()
    {

        if (isset($_POST["button"])) {

            if (!isset($_POST["userList"])) {
                return "NO_USER_LIST";
            }

            if ($this->createNotification($_POST["title"], $_POST["body"], $_POST["userList"])) {
                return true;
            }
        }

        return false;


    }


    public function processAction($assign)
    {
        switch ($assign->get("action")) {
            case "view":

                break;

        }
    }


    public function deleteAndRedirect($id)
    {
        $this->delete($id);
        send_to("notifications.php");
    }


    public function delete($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE user_has_notification SET deleted = 1 WHERE notification_id = :id AND user_id = :user_id ";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->bindParam(":user_id", Session::userData()->idrep);
        $prep->execute();
    }


    public function markAndRedirect($id)
    {
        $this->markAsRead($id);
        send_to("notifications.php");
    }

    public function markAsRead($id)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "UPDATE user_has_notification SET seen = 1 WHERE notification_id = :id AND user_id = :user_id ";
        $prep = $db->prepare($sql);
        $prep->bindParam(":id", $id);
        $prep->bindParam(":user_id", Session::userData()->idrep);
        $prep->execute();

    }


    public static function sendNotification($to, int $from, string $title, string $message)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "INSERT INTO notifications (title, body, timestamp, author) VALUES(:title, :body, :timestamp, :author)";

        $timestamp = date("U");

        $prep = $db->prepare($sql);
        $prep->bindParam(":title", $title);
        $prep->bindParam(":body", $message);
        $prep->bindParam(":timestamp", $timestamp);
        $prep->bindParam(":author", $from);

        if ($prep->execute()) {
            $notification_id = $db->lastInsertId();

            $sql = "INSERT INTO user_has_notification(notification_id, user_id) VALUES";

            if (is_array($to) && !empty($to)) {


                for ($i = 0; $i < count($to); $i++) {
                    $questionMarks[] = "(?,?)";
                    $insertValues[] = $notification_id;
                    $insertValues[] = $to[$i];
                }

                $sql = "INSERT INTO user_has_notification(notification_id, user_id) VALUES".implode(',',
                        $questionMarks);

                $prep = $db->prepare($sql);

                return $prep->execute($insertValues);

            } else {
                $sql .= "(:notification_id, :user_id)";
                $prep = $db->prepare($sql);
                $prep->bindParam(":notification_id", $notification_id);
                $prep->bindParam(":user_id", $to);

                return $prep->execute();
            }

        } else {
            return false;
        }
    }


    public function createNotification($title, $body, $users)
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();

        $title = xss_clean($title);
        $body = xss_clean($body);
        $timestamp = date("U");

        $this->newNotification["title"] = $title;
        $this->newNotification["body"] = $body;
        $this->newNotification["user_name"] = Session::userData()->user_name;


        $sql = "INSERT INTO notifications (title, body, timestamp, author) VALUES(:title, :body, :timestamp, :author)";
        $prep = $db->prepare($sql);

        $prep->bindParam(":title", $title);
        $prep->bindParam(":body", $body);
        $prep->bindParam(":timestamp", $timestamp);
        $prep->bindParam(":author", $this->userID);

        $prep->execute();

        $notificationID = $db->lastInsertId();


        for ($i = 0; $i < count($users); $i++) {
            $questionMarks[] = "(?,?)";
            $insertValues[] = $notificationID;
            $insertValues[] = $users[$i];
        }

        $sql = "INSERT INTO user_has_notification(notification_id, user_id) VALUES".implode(',', $questionMarks);

        $prep = $db->prepare($sql);

        $prep->execute($insertValues);

        if (isset($_POST["sendEmails"]) && isset($_POST["userList"])) {

            $sql = "SELECT email FROM rep WHERE idrep = ";

            // find emails associated with rep ids..
            for ($i = 0; $i <= count($users) - 1; $i++) {
                if ($i !== count($users) - 1) {
                    $sql .= " ? OR idrep = ";
                } else {
                    $sql .= " ? ";
                }
            }


            $prep = $db->prepare($sql);
            $prep->execute($users);

            $emails = array();
            $emails = $this->appendMultiDimentialToArray($prep->fetchAll(PDO::FETCH_ASSOC), $emails);

            $this->massMail($emails);

        }

        return true;
    }


    public function massMail($mailerList)
    {
        $htmlBody = "<html><h3>Notification from {$this->newNotification['user_name']} @ {$_SERVER["HTTP_HOST"]}</h3><br/>{$this->newNotification['body']}</html>";

        foreach ($mailerList as $address) {
            if (filter_var($address, FILTER_VALIDATE_EMAIL)) {
                $mail = new Mail($address, $this->newNotification['title'], $htmlBody);
                $mail->send();
            }
        }
    }


    public function dumpNotifications()
    {
        dd($this->notifications);
    }

    public function getInboxCount()
    {
        $count = 0;
        foreach ($this->notifications as $notification) {
            if ($notification["seen"] == 0) {
                $count++;
            }
        }

        return $count;
    }

    public function printToInbox()
    {
        $webroot = getWebRoot();
        $i = 0;
        foreach ($this->notifications as $notification) {
            if ($notification["seen"] == 0) {
                $i++;
                if ($i <= 5) // only allow 5 to be shown
                {
                    $carboon = \Carbon\Carbon::createFromTimestamp($notification["timestamp"])->diffForHumans();
                    $title = charLimit($notification["title"], 30);
                    $body = charLimit($notification["body"], 15);

                    echo " <li>
                        <img src=\"{$webroot}/images/icon-mail.png\" alt=\"\"><p><a href='/notifications.php?action=view&id={$notification["id"]}'>{$title}</a></p><p class=\"time\">{$carboon}</p>
                    </li>";
                } else {
                    break;
                }
            }

        }
    }


    public function printToTable()
    {
        foreach ($this->notifications as $notification) {
            $body = charLimit($notification["body"], 25);
            $date = \Carbon\Carbon::createFromTimestamp($notification["timestamp"])->toFormattedDateString();

            if ($notification["seen"] == 1) {
                echo "<tr>";
            } else {
                echo "<tr class='bg-success'>";
            }

            echo "<td>{$notification["title"]}</td>";
            echo "<td>{$body}</td>";
            echo "<td>{$date}</td>";
            echo "<td>{$notification["author_user_name"]}</td>";
            echo "<td>
                     
                                <a class='btn btn-default btn-sm' href='notifications.php?action=view&id={$notification["id"]}'>View</a>

                        <a class='btn btn-default btn-sm' href='notifications.php?action=mark&id={$notification["id"]}'>Mark as Read</a>
                        <a class='btn btn-default btn-sm' onclick='confirmPlease({$notification["id"]});' href='javascript:void(0);'>Delete</a>
                    
                </td>";
            echo "</tr>";
        }
    }

    public function fetchUsersNotifications()
    {
        $db = \LeadMax\TrackYourStats\Database\DatabaseConnection::getInstance();
        $sql = "SELECT user_has_notification.notification_id, user_has_notification.user_id, user_has_notification.seen,
                notifications.id, notifications.title, notifications.body, notifications.timestamp, rep.user_name as author_user_name
 
             FROM user_has_notification 
                  INNER JOIN notifications ON notifications.id = user_has_notification.notification_id 
                  INNER JOIN rep ON rep.idrep = notifications.author
          WHERE user_id = :userID AND deleted = 0 
          ORDER BY timestamp DESC";


        $prep = $db->prepare($sql);
        $prep->bindParam(":userID", $this->userID);

        if ($prep->execute()) {
            $this->notifications = $prep->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $this->notifications = false;
        }
    }


    private function appendMultiDimentialToArray($multiDimentialArray, $userIDList)
    {
        foreach ($multiDimentialArray as $key => $val) {
            foreach ($val as $key2 => $valFinal) {
                $userIDList[] = $valFinal;
            }
        }

        return $userIDList;
    }

    private function findUserTypeAndReturnSQL($name)
    {
        switch ($name) {
            case "quack":
                return " privileges.is_admin = 1 ";
                break;

            case "funky":
                return " privileges.is_manager = 1 ";
                break;

            case "duck":
                return " privileges.is_rep = 1 ";
                break;
        }
    }


}