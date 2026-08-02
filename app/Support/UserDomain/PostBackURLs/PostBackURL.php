<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/1/2018
 * Time: 12:11 PM
 */

namespace App\Support\UserDomain\PostBackURLs;

abstract class PostBackURL
{

    abstract function getPriorityURL();

    abstract function getGlobalURL();

    abstract function getOfferSpecificURL();

    abstract function updateGlobalURL($url);

    abstract function updateOfferURL($url);

}
