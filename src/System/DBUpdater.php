<?php

namespace LeadMax\TrackYourStats\System;

use LeadMax\TrackYourStats\Database\DatabaseConnection;

/**
 * Author: Dean
 * Email: dwm348@gmail.com
 * Date: 10/5/2017
 * Time: 2:15 PM
 */
class DBUpdater
{

    const versionDirectory = "resources/database/";

    public $versions = array();

    public $latest = 0;

    private $companyList = array();

    private $needUpdates = array();


    public function __construct()
    {
        $this->findVersions();

        $this->findLatest();

        $this->findCompanies();

        $this->checkCompanyVersions();

    }

    public function dumpNeedUpdates()
    {
        dd($this->needUpdates);
    }

    public function updateAll()
    {
        foreach ($this->needUpdates as $company) {
            if (!$this->updateCompany($company["subDomain"], $company["db_version"])) {
                return false;
            }
        }

        return true;
    }

    private function checkCompanyVersions()
    {
        foreach ($this->companyList as $company) {
            if ($company["db_version"] < $this->getLatest()) {
                $this->needUpdates[] = $company;
            }
        }
    }

    private function findCompanies()
    {
        $db = DatabaseConnection::getMasterInstance();
        $sql = "SELECT subDomain, db_version FROM company";

        $stmt = $db->prepare($sql);
        $stmt->execute();

        $this->companyList = $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }


    private function updateCompany($SUB_DOMAIN, $COMPANY_VERSION)
    {
        $connection = Connection::createConnectionWithSubDomain($SUB_DOMAIN);
        $connection->beginTransaction();

        $steps = $this->getUpdateSteps($COMPANY_VERSION);


        foreach ($steps as $version) {
            try {

                $query = file_get_contents(self::versionDirectory.$version."/script.sql");
                $stmt = $connection->prepare($query);
                if ($stmt->execute()) {
                    $this->updateCompanyToVersion($SUB_DOMAIN, $version);
                } else {
                    $connection->rollBack();
                    var_dump($SUB_DOMAIN);
                    var_dump($connection->errorInfo());
                    var_dump($connection->errorCode());

                    return false;
                }

            } catch (\Exception $e) {
                Log($e, $this);

                return false;
            }

        }

        $connection->commit();

        return true;
    }


    private function updateCompanyToVersion($SUB_DOMAIN, $VERSION)
    {
        $db = DatabaseConnection::getMasterInstance();
        $sql = "UPDATE company SET db_version = :db_version WHERE subDomain = :subDomain";
        $prep = $db->prepare($sql);
        $prep->bindParam(":db_version", $VERSION);
        $prep->bindParam(":subDomain", $SUB_DOMAIN);
        if ($prep->execute()) {
            echo "Updated {$SUB_DOMAIN} to v{$VERSION} <br/>";
        } else {
            echo "ERROR UPDATING {$SUB_DOMAIN} TO v{$VERSION} ";
            dd($db->errorInfo());
        }
    }


    // INPUT: Company Database Version
    // OUTPUT: Returns array of versions needed to get to latest
    /* EXAMPLE:
         currentVersions = 1.2, 1.5, 1.6, 1.8
         $COMPANY_VERSION = 1.5
         function will return array of [1.6, 1.8]
         these are the 'steps' that the db needs to go through to get to latest version

    */
    private function getUpdateSteps($COMPANY_VERSION)
    {
        $newVersions = array();
        foreach ($this->versions as $version) {
            if ($COMPANY_VERSION < $version) {
                $newVersions[] = $version;
            }
        }

        return $newVersions;
    }


    public function getLatest()
    {
        return $this->latest;
    }

    private function findVersions()
    {
        $filtered = array();

        $versions = scandir(self::versionDirectory);

        // filter dots
        foreach ($versions as $version) {
            if ($version !== ".." && $version !== ".") {
                $filtered[] = (double)$version;
            }
        }

        $this->versions = $filtered;
    }

    private function findLatest()
    {
        $this->latest = $this->versions[count($this->versions) - 1];
    }


}