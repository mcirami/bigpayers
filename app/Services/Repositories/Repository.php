<?php


namespace App\Services\Repositories;


use Carbon\Carbon;

/**
 * Repositories are classes to store and use complicated database queries.
 * @package App\Services\Repositories
 */
interface Repository
{
    /**
     * Return the Eloquent query builder.
     * @param Carbon $start
     * @param Carbon $end
     * @return Illuminate\Database\Query\Builder|Illuminate\Database\Eloquent\Builder;
     */
    public function query(Carbon $start, Carbon $end) ;

    /**
     * Fetch results with the given dates.
     * @param Carbon $start
     * @param Carbon $end
     * @return mixed
     */
    public function between(Carbon $start, Carbon $end);
}