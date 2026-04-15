<?php

namespace LeadMax\TrackYourStats\Report\Repositories;

use App\Privilege;
use App\User;

class AggregateReportRepository extends Repository
{
    /* @var User */
    private $user;

    /**
     * Sets the user to be used in the query.
     * @param User $user
     */
    public function setUser(User $user)
    {
        $this->user = $user;
    }

    protected function query($dateFrom, $dateTo): \PDOStatement
    {
        $db = $this->getDB();

        $role = $this->user->getRole();
        if ($role == Privilege::ROLE_AFFILIATE) {
            $constraint = "WHERE user_id = :user_id";
        } else {
            $constraint = "LEFT JOIN rep ON rep.lft > :left AND rep.rgt < :right WHERE user_id = rep.idrep";
        }

        $sql = "
            SELECT 
               aggregate_date,
               sum(clicks) clicks,
               sum(unique_clicks) unique_clicks,
               sum(free_sign_ups) free_sign_ups,
               sum(pending_conversions) pending_conversions,
               sum(conversions) conversions,
               sum(revenue) revenue,
               sum(deductions) deductions
            FROM aggregate_reports  
             " . $constraint . "
            AND aggregate_date BETWEEN :startDate AND :endDate
            GROUP BY aggregate_date
        ";

        $stmt = $db->prepare($sql);
        if ($role == Privilege::ROLE_AFFILIATE) {
            $stmt->bindValue(':user_id', $this->user->idrep);
        } else {
            $stmt->bindValue(':left', $this->user->lft);
            $stmt->bindValue(':right', $this->user->rgt);
        }
        $stmt->bindValue(':startDate', $dateFrom);
        $stmt->bindValue(':endDate', $dateTo);
        $stmt->execute();

        return $stmt;
    }

    public function between($dateFrom, $dateTo): array
    {
        return $this->query($dateFrom, $dateTo)->fetchAll(\PDO::FETCH_ASSOC);
    }
}