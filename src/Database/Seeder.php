<?php
/**
 * Created by PhpStorm.
 * User: professional slacker
 * Date: 3/19/2018
 * Time: 4:41 PM
 */

namespace LeadMax\TrackYourStats\Database;


use LeadMax\TrackYourStats\Database\Stubs\Stub;

class Seeder
{

    public $stubs = [];


    public $report = [];

    public function __construct()
    {

    }

    public function addStub(Stub $stub)
    {
        $this->stubs[] = $stub;
    }

    public function seed()
    {
        foreach ($this->stubs as $stub) {
            $stub->run();
            $this->report[] = $stub->generateReport();
        }
    }


}