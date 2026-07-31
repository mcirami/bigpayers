<?php

namespace App\Support\Report;


use App\Support\Report\Filters\Filter;
use App\Support\Report\Formats\Format;
use App\Support\Report\Repositories\Repository;

class Reporter
{

    private $repo;

    private $filters = array();

    private $closures = array();


    public function __construct(Repository $repository)
    {
        $this->repo = $repository;
    }


    public function addFilter($filter)
    {
        if ($filter instanceof Filter || $filter instanceof \Closure) {
            $this->filters[] = $filter;
        }

        return $this;
    }


    private function filterReport($report)
    {
        foreach ($this->filters as $filter) {
            if ($filter instanceof Filter) {
                $report = $filter->filter($report);
            } else if ($filter instanceof \Closure) {
                $report = $filter($report);
            }
        }


        return $report;
    }

    public function fetchReport($dateFrom, $dateTo): array
    {
        $report = $this->repo->between($dateFrom, $dateTo);

        
        $report = $this->filterReport($report);

        return $report;
    }

    public function between($dateFrom, $dateTo, Format $format)
    {

        $report = $this->repo->between($dateFrom, $dateTo);

        $report = $this->filterReport($report);

        return $format->output($report);
    }

    public function count($dateFrom, $dateTo)
    {
        return $this->repo->count($dateFrom, $dateTo);
    }


}
