<?php

namespace LeadMax\TrackYourStats\Database;

use LeadMax\TrackYourStats\System\Connection;

class CompanyUpdater
{
    /**
     * Array of companies to update
     * @var array
     */
    public $companies = array();


    /**
     * Array of Versions' class names to disregard when updating
     * Generally, these versions were for a one time hot-fix and
     * are no longer needed and/or too expensive to run
     * e.g. [  V164::class, V145::class ]
     * @var array
     */
    public $blackList = array(
        Versions\V06::class,
        Versions\V131::class,
        Versions\V134::class,
        Versions\V138::class,
        Versions\V152::class,
        Versions\V162::class,
        Versions\V164::class,
        Versions\V165::class,
        Versions\V154::class,
        Versions\V167::class,
    );

    public function __construct()
    {
        $this->findCompanies();
    }

    /**
     * Loops through public $companies and creates an instance of
     * Updater with a PDO Connection generated from the company's sub domain
     * Then finds required updates for the company and sets the updater to the array
     * e.g. output : [ 'trackyourstats' => [ 'required_updates' => [], 'valid_updates' => [], 'updater' => (object)       ]         ]
     * @return array
     */
    public function findRequiredUpdates()
    {
        $report = array();
        foreach ($this->companies as $company) {
            $con = Connection::createConnectionWithSubDomain($company["subDomain"]);
            $updater = new Updater($con);
            $updater->blackList = $this->blackList;
            $updater->getVersions();
            $report[$company["subDomain"]] = $updater->findRequiredAndValidUpdates();
            $report[$company['subDomain']]['updater'] = $updater;
        }

        return $report;
    }

    /**
     * Updates companies and returns a report
     * @return array
     */
    public function updateCompanies()
    {
        $report = array();

        foreach ($this->findRequiredUpdates() as $companySubDomain => $company) {
            $report[$companySubDomain] = array_merge($company, $company["updater"]->updateDatabase());
            $this->updateCompanyToVersion($companySubDomain, $company['updater']->getLatestVersion());
        }

        return $report;
    }

    /**
     * Updates company version in companies table to $VERSION
     * @param $SUB_DOMAIN
     * @param $VERSION
     * @return bool
     */
    private function updateCompanyToVersion($SUB_DOMAIN, $VERSION)
    {
        $db = DatabaseConnection::getMasterInstance();
        $sql = "UPDATE company SET db_version = :db_version WHERE subDomain = :subDomain";
        $prep = $db->prepare($sql);
        $prep->bindParam(":db_version", $VERSION);
        $prep->bindParam(":subDomain", $SUB_DOMAIN);

        return $prep->execute();
    }

    /**
     * Finds company installs in master database and sets an assoc
     * array to public $companies
     * @param bool $specificSubDomain
     */
    private function findCompanies($specificSubDomain = false)
    {
        $db = DatabaseConnection::getMasterInstance();

        $sql = "SELECT subDomain, db_version FROM company";

        if (is_string($specificSubDomain)) {
            $sql .= " WHERE subDomain = :subDomain ";
        }


        $stmt = $db->prepare($sql);

        if ($specificSubDomain) {
            $stmt->bindParam(":subDomain", $specificSubDomain);
        }


        $stmt->execute();

        $this->companies = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

}