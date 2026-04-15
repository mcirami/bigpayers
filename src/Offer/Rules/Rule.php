<?php namespace LeadMax\TrackYourStats\Offer\Rules;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/10/2017
 * Time: 12:15 PM
 */
interface Rule
{


    function __construct($rules);

    function checkRules();

    function getRedirectOffer();

    // function processRules() // filter pass $rules from constructor

}