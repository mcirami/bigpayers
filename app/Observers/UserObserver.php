<?php

namespace App\Observers;

use PDO;

class UserObserver
{

    /**
     *  TODO: Stuff that's going to be done in clean up branch. Here as just a note/reminder.
     */
    public function TODO()
    {
        /*
        if ($repType == Privilege::ROLE_AFFILIATE) {
            RepHasOffer::assignAffiliateToPublicOffers($repID);

            ReportPermissions::createPermissions($repID);
        }


        Bonus::assignUsersInheritableBonuses([$repID], $referrer_repid);
         */
    }

    public function created()
    {
        $this->rebuild_tree(1, 1);
    }

    public function updated()
    {
        $this->rebuild_tree(1, 1);
    }

    public function saved()
    {
        $this->rebuild_tree(1, 1);
    }

    public function deleted()
    {
        $this->rebuild_tree(1, 1);
    }


    /**
     * Rebuilds the User Hierarchy tree.
     * TODO: Should be refactored.
     * @param $referrer_repid
     * @param $left
     * @return int|mixed
     */
    function rebuild_tree($referrer_repid, $left)
    {
        $db = \DB::getPdo();
        // the right value of this node is the left value + 1
        $right = $left + 1;

        $sql = 'SELECT idrep FROM rep WHERE referrer_repid= :parent ';
        $prep = $db->prepare($sql);
        $prep->bindParam(":parent", $referrer_repid);
        $prep->execute();


        // get all children of this node
        $result = $prep->fetchAll(PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            // recursive execution of this function for each
            // child of this node
            // $right is the current right value, which is
            // incremented by the rebuild_tree function
            $right = $this->rebuild_tree($row['idrep'], $right);
        }

        // we've got the left value, and now that we've processed
        // the children of this node we also know the right value
        $sql = "UPDATE rep SET lft=:left, rgt=
        :right WHERE idrep=:parent ";

        $prep = $db->prepare($sql);
        $prep->bindParam(":left", $left);
        $prep->bindParam(":right", $right);
        $prep->bindParam(":parent", $referrer_repid);

        $prep->execute();

        // return the right value of this node + 1
        return $right + 1;
    }
}