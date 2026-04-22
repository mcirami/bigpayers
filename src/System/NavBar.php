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

        "Account" => [
            "css" => "fas fa-building",

            "My Account" => ['url' => '/dashboard'],

            "Offer URLs" => ['url' => '/offer/urls', 'required_permissions' => ['edit_offer_urls']],

            "Add Sale" => ['url' => '/sales/add', 'required_permissions' => [Permissions::ADJUST_SALES]],

            "Verification" => ['url' => '/verification', 'required_user_types' => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_AFFILIATE], 'required_permissions' => [Permissions::SMS_CHAT]],

            /*"Notifications" => ['url' => '/notifications'],*/

            "IP Blacklist" => ['url' => '/ip-blacklist', "required_user_types" => [\App\Privilege::ROLE_GOD]],

            "Settings" => ["url" => "/settings", "required_user_types" => [\App\Privilege::ROLE_GOD]],
        ],

        "Reports" => [
            "css" => "far fa-file-alt",

            "Agent Report" => [
                'url' => '/report/affiliate',
                "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, \App\Privilege::ROLE_MANAGER],
            ],

            "Offer Report" => [
                'url' => '/report/offer',
            ],

            "Country Report" => [
	            'url' => '/report/geo',
	            "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
            ],

            "Advertiser Report" => [
                'url' => '/report/advertiser',
                "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
                "required_permissions" => ["view_adv_reports"],
            ],

            "Blacklist Report" => ["url" => "/report/blacklist", "required_user_types" => [\App\Privilege::ROLE_GOD]],

            "Adjustments Log" => [
                'url' => '/report/adjustments',
                "required_user_types" => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN],
                "required_permissions" => [Permissions::ADJUST_SALES],
            ],

            "Sub Report" => ['url' => '/report/sub', "required_user_types" => [\App\Privilege::ROLE_AFFILIATE]],

            "Payout Report" => ['url' => '/report/payout', "required_user_types" => [\App\Privilege::ROLE_AFFILIATE]],
        ],

        "Users" => [

            "css" => "fas fa-users",

            'required_user_types' => [\App\Privilege::ROLE_GOD, \App\Privilege::ROLE_ADMIN, \App\Privilege::ROLE_MANAGER],

            "Manage Users" => [
                'url' => '/user/manage',
            ],

            "Create Users" => [
                'url' => '/user/create',
                'required_permissions' => ['create_affiliates'],
            ],

            "Pending Users" => [
                'url' => '/user/pending',
                'required_permissions' => ['approve_affiliate_sign_ups'],
            ],

            "Banned Users" => [
                'url' => '/user/banned',
                'required_permissions' => [Permissions::BAN_USERS],
            ],

        ],

        "Offers" => [
            "css" => "fas fa-tag",

            "Manage Offers" => [
                'url' => '/offer/manage',
            ],

            'Create Offers' => [
                'url' => '/offer/create',
                'required_permissions' => ['create_offers'],
            ],

            'Multi-Assign Offers' => [
                'url' => '/offer/mass-assign',
                'required_permissions' => ['create_offers'],
            ],

            "Click Search" => [
                'url' => "/click-search",
                "required_user_types" => [\App\Privilege::ROLE_GOD],
            ],

            "Global PostBack" => [
                'url' => '/global-postback',
                'required_user_types' => [\App\Privilege::ROLE_AFFILIATE],
            ],

        ],

        "Advertisers" => [

            "required_user_types" => [\App\Privilege::ROLE_GOD],

            "css" => "fas fa-bullhorn",

            "Manage Advertisers" => ['url' => "/advertisers"],

            "Create Advertisers" => ['url' => "/advertisers/create"],

        ],

    );


    function __construct($userType, $permissions)
    {
        // initialize date fields
        $this->dateFrom = Date::today();
        $this->dateTo = Date::today();


        $this->userType = $userType;
        $this->permissions = $permissions;;
        if (isset($this->menu['Reports']['Agent Report'])) {
            $affiliateReportLabel = config('branding.affiliate.singular') . ' Report';
            $reports = [];

            foreach ($this->menu['Reports'] as $key => $value) {
                if ($key === 'Agent Report') {
                    $reports[$affiliateReportLabel] = $value;
                    continue;
                }

                $reports[$key] = $value;
            }

            $this->menu['Reports'] = $reports;
        }

        $this->currentPage = parse_url($_SERVER["REQUEST_URI"])["path"];


    }

    public function getVisibleMenu()
    {
        $sections = [];

        foreach ($this->menu as $menuName => $menuItems) {
            if (!$this->checkUserType($menuItems) || !$this->checkPermissions($menuItems) || !$this->checkPossiblePermissions($menuItems)) {
                continue;
            }

            $section = [
                'name' => $menuName,
                'icon' => $menuItems['css'] ?? '',
                'items' => [],
            ];

            foreach ($menuItems as $key => $vals) {
                if (!$this->isMenuItem($key) || !$this->checkPermissions($vals) || !$this->checkUserType($vals) || !$this->checkPossiblePermissions($vals)) {
                    continue;
                }

                $url = $vals['url'];

                if ($this->hasDateOptions($vals)) {
                    $url .= "?d_from={$this->dateFrom}&d_to={$this->dateTo}";
                }

                $section['items'][] = [
                    'name' => $key,
                    'url' => $this->webRoot . $url,
                    'active' => $vals['url'] == $this->currentPage,
                ];
            }

            if (!empty($section['items'])) {
                $sections[] = $section;
            }
        }

        return $sections;
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
