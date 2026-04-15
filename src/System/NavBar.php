<?php

namespace LeadMax\TrackYourStats\System;

use LeadMax\TrackYourStats\Table\Date;
use LeadMax\TrackYourStats\User\Permissions;

class NavBar
{
    public $currentPage = "";

    public $userType;

    public $permissions;

    public $dateFrom;

    public $dateTo;

    private $webRoot = "";

    public $menu = array(

        "Users" => [

            "css" => "fas fa-users",

            'required_user_types' => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, \App\Privilege::ROLE_MANAGER],

            "Manage Users" => [
                'url' => '/user/manage',
            ],


            "Create Users" => [
                'url' => '/aff_add.php',
                'required_permissions' => ['create_affiliates'],
            ],

            //			"Report Permissions" => [
            //				'url'                  => 'aff_permissions.php',
            //				"required_permissions" => [Permissions::EDIT_REPORT_PERMISSIONS]
            //			],

            "Pending Users" => [
                'url' => '/view_pending_affiliates.php',
                'required_permissions' => ['approve_affiliate_sign_ups'],
            ],

            "Banned Users" => [
                'url' => '/banned_users.php',
                'required_permissions' => [Permissions::BAN_USERS],
            ],

        ],

        "Offers" => [
            "css" => "fas fa-tag",

            "Manage Offers" => [
                'url' => '/offer/manage',
            ],

            'Create Offers' => [
                'url' => '/offer_add.php',
                'required_permissions' => ['create_offers'],
            ],


            "Global PostBack" => [
                'url' => '/global_postback.php',
                'required_user_types' => [\App\Privilege::ROLE_AFFILIATE],
            ],

            'Multi-Assign Offers' => [
                'url' => '/offer/mass-assign',
                'required_permissions' => ['create_offers'],
            ],

    //        'Mass Assign PostBack' => [
    //            'url' => '/mass_assign_pb.php',
    //            'required_user_types' => [\App\Privilege::ROLE_AFFILIATE],
    //        ],

            "Click Search" => [
                'url' => "/clicksearch.php",
                "required_user_types" => [\App\Privilege::ROLE_GOD],
            ],

        ],

        "Reports" => [
            "css" => "far fa-file-alt",

            "Advertiser Report" => [
                'url' => '/report/advertiser',
                "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
                "required_permissions" => ["view_adv_reports"],
            ],


            "Offer Report" => [
                'url' => '/report/offer',
            ],


            "Sub Report" => ['url' => '/report/sub', "required_user_types" => [\App\Privilege::ROLE_AFFILIATE]],

            "Agent Report" => [
                'url' => '/report/affiliate',
                "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, \App\Privilege::ROLE_MANAGER],
            ],

            "GEO Report" => [
	            'url' => '/report/geo',
	            "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
            ],

            "Payout Report" => ['url' => '/report/payout', "required_user_types" => [\App\Privilege::ROLE_AFFILIATE]],

            "Blacklist Report" => ["url" => "/report/blacklist", "required_user_types" => [\App\Privilege::ROLE_GOD]],

            "Adjustments Log" => [
                'url' => '/report/adjustments',
                "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
                "required_permissions" => [Permissions::ADJUST_SALES],
            ],

     //       "Chat Log Report" => [
     //           'url' => '/report/chat-log',
     //           "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, \App\Privilege::ROLE_MANAGER],
     //       ]
     //       ,
     //
     //       "Sale Log" => [
     //           'url' => '/report/sale-log',
     //           'required_user_types' => [\App\Privilege::ROLE_AFFILIATE],
     //       ],

            "Daily Report" => [
                'url' => '/report/daily',
            ]

        ],

        "Advertisers" => [

            "required_user_types" => [\App\Privilege::ROLE_GOD],

            "css" => "fas fa-bullhorn",

            "Manage Advertisers" => ['url' => "/campaign_manage.php"],

            "Create Advertisers" => ['url' => "/campaign_create.php"],

        ],

        "Account" => [
            "css" => "fas fa-building",

            "My Account" => ['url' => '/dashboard'],

//            "SMS Chat" => ['url' => '/sms', 'required_user_types' => [\App\Privilege::ROLE_AFFILIATE], 'required_permissions' => [Permissions::SMS_CHAT]],

            "Verification" => ['url' => '/verification', 'required_user_types' => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_AFFILIATE], 'required_permissions' => [Permissions::SMS_CHAT]],

//            "Email Pools" => ['url' => '/email/pools', 'required_permissions' => [Permissions::EMAIL_POOLS]],

//            "Assign SMS" => ['url' => '/sms/client/add', 'required_user_types' => [\App\Privilege::ROLE_GOD]],

            "Add Sale" => ['url' => '/sales/add', 'required_permissions' => [Permissions::ADJUST_SALES]],

            "Offer URLs" => ['url' => '/offer_urls.php', 'required_permissions' => ['edit_offer_urls']],

            "IP Blacklist" => ['url' => '/ip_black_list.php', "required_user_types" => [\App\Privilege::ROLE_GOD]],

            "Notifications" => ['url' => '/notifications.php'],

//            "Salaries" => ["url" => "/salaries.php", "possible_permissions" => ["pay_salaries"]],

//            "Bonuses" => ["url" => "/bonus.php", "possible_permissions" => ["create_bonuses", "assign_bonuses"]],

            "Settings" => ["url" => "/settings.php", "required_user_types" => [\App\Privilege::ROLE_GOD]],

        ],


    );


    function __construct($userType, $permissions)
    {
        // initialize date fields
        $this->dateFrom = Date::today();
        $this->dateTo = Date::today();


        $this->userType = $userType;
        $this->permissions = $permissions;;

        $this->currentPage = parse_url($_SERVER["REQUEST_URI"])["path"];


    }


    public function printNav($mobile = false)
    {

        foreach ($this->menu as $menuName => $menuItems) {

            if ($this->checkUserType($menuItems) && $this->checkPermissions($menuItems) && $this->checkPossiblePermissions($menuItems)) {


                $this->printMenuStart($menuName, $menuItems["css"], $mobile);


                foreach ($menuItems as $key => $vals) {
                    if ($this->isMenuItem($key)) {

                        if ($this->checkPermissions($vals) && $this->checkUserType($vals) && $this->checkPossiblePermissions($vals)) {

                            $this->printSubMenuItem($key, $vals["url"], $this->hasDateOptions($vals), $mobile);

                        }
                    }

                }
                echo "</ul></li>";

            }


        }
    }


    private function hasDateOptions($items)
    {
        if (in_array("opt_dates", $items)) {
            return true;
        } else {
            return "";
        }
    }

    private function isMenuItem($str)
    {
        if ($str !== "css" && $str !== "required_user_types" && $str !== "required_permissions") {
            return true;
        } else {
            return false;
        }
    }


    private function printMenuStart($name, $css, $mobile = false)
    {

        if ($mobile) {
            echo "<li class=\"drawer-dropdown\">
                <a class=\"drawer-menu-item value_span2-2 value_span2 value_span4 value_span4 value_span6-1\"
                   data-target=\"#\" href=\"#\" data-toggle=\"dropdown\" role=\"button\" aria-expanded=\"false\">{$name}<span class=\"drawer-caret\"></span></a>
                <ul class=\"drawer-dropdown-menu value_span4-2\">";
        } else {
            echo "   <li class=\"dropdown value_span6-3\">
                <a class=\"value_span2-2 value_span3-2 value_span5 value_span6\" href=\"#\"><span
                           ><i class=\"{$css}\"
                                                  aria-hidden=\"true\"></i><b>{$name}</b></span></a>
                <ul class=\"dropdown-menu value_span6-1 value_span6-4\">";
        }


    }


    private function printSubMenuItem($name, $url, $dates = false, $mobile = false, $css = "")
    {
        $isSelected = ($url == $this->currentPage) ? "active value_span1 value_span2 value_span6-1 " : "";


        if ($dates) {
            $url .= "?d_from={$this->dateFrom}&d_to={$this->dateTo}";
        }

        if ($mobile) {
            echo " <li>
                        <a class=\"drawer-dropdown-menu-item value_span2 value_span2-2 value_span3-2 value_span4 value_span5 {$css}
                       \" href=\"{$this->webRoot}{$url}\">{$name}</a></li>";
        } else {
            echo "<li>
                <a class=\"{$css} value_span2-2 value_span3-2 value_span4 value_span2 value_span6 {$isSelected}\" href=\"{$this->webRoot}{$url}\">{$name}</a>
            </li>";
        }
    }

    private function checkPossiblePermissions($menuArray)
    {
        if (isset($menuArray["possible_permissions"])) {
            foreach ($menuArray["possible_permissions"] as $permission) {
                if ($this->permissions->can($permission)) {
                    return true;
                }
            }

            return false;
        } else {
            return true;
        }
    }

    private function checkPermissions($menuArray)
    {
        if (isset($menuArray["required_permissions"])) {
            foreach ($menuArray["required_permissions"] as $permission) {
                if (!$this->permissions->can($permission)) {
                    return false;
                }
            }

            return true;
        } else {
            return true;
        }

    }

    private function checkUserType($menuArray)
    {
        if (isset($menuArray["required_user_types"])) {

            if (in_array($this->userType, $menuArray["required_user_types"])) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }


}