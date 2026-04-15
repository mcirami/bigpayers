<?php

namespace App\Providers;

use App\Observers\UserObserver;
use App\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use LeadMax\TrackYourStats\System\NavBar;
use LeadMax\TrackYourStats\System\Notifications;
use LeadMax\TrackYourStats\System\Session;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        View::share('webroot', getWebRoot());
        view()->composer('layouts.master', function (\Illuminate\View\View $view) {
            $navBar = new NavBar(Session::userType(), Session::permissions());
            $notifications = new Notifications(Session::userID());
            $notifications->fetchUsersNotifications();
            $view->with(['navBar' => $navBar, 'notifications' => $notifications]);
        });
        User::observe(UserObserver::class);

	    Paginator::defaultView('vendor/pagination/default');
	    Paginator::defaultSimpleView('default');
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
