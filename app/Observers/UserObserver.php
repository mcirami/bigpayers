<?php

namespace App\Observers;

use App\User;
use App\Support\LegacyTree as Tree;

class UserObserver
{
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
