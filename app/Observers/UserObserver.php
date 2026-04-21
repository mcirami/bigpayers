<?php

namespace App\Observers;

use App\User;
use LeadMax\TrackYourStats\User\Tree;

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

    public function saved(User $user): void
    {
        if ($user->wasRecentlyCreated || $user->wasChanged(['referrer_repid', 'status'])) {
            Tree::rebuild_tree(1, 1);
        }
    }

    public function deleted(User $user): void
    {
        Tree::rebuild_tree(1, 1);
    }
}
