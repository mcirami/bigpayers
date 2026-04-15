<?php

namespace LeadMax\TrackYourStats\Database;


class Updater
{

    /**
     * Directory of Versions used when updating databases, must have *.php at the end!
     * @var string
     */
    public $versionsDirectory = '../src/Database/Versions/*.php';

    /**
     * List of Versions found in $versionsDirectory and not in $blackList
     * @var array
     */
    public $versions = array();


    /**
     * PDO Database connection to run updates on
     * @var array
     */
    public $connection;

    /**
     * Array of Versions' class names to disregard when updating
     * Generally, these versions were for a one time hot-fix and
     * are no longer needed and/or too expensive to run
     * e.g. [  V164::class, V145::class ]
     * @var array
     */
    public $blackList = [];


    /**
     * Updater constructor. Accepts a PDO connection
     *
     * @param $connection
     */
    public function __construct(\PDO $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Loops through public $versions and finds the latest version number
     * @return float
     */
    public function getLatestVersion()
    {
        $latestVersion = 0.00;
        foreach ($this->versions as $version) {
            if ($version->getVersion() > $latestVersion) {
                $latestVersion = $version->getVersion();
            }
        }

        return $latestVersion;
    }

    /**
     * Updates database public $connection with 'required_updates' from findRequiredAndValidUpdates()
     * @return array
     */
    public function updateDatabase()
    {
        $success = [];
        $failure = [];
        foreach ($this->findRequiredAndValidUpdates()['required_updates'] as $version) {
            if ($version->update()) {
                if ($version->verifyUpdate()) {
                    $success[] = get_class($version);
                } else {
                    $failure[] = get_class($version);
                }
            }
        }

        return ['success' => $success, 'failure' => $failure];
    }

    /**
     * Returns a report of required updates for public $connection
     * Does not actually update database, used to just see what updates are needed
     * e.g. output : [ 'required_updates' => [V135, V136], 'valid_updates' => [ V133, V134 ] ]
     * @return array
     */
    public function findRequiredAndValidUpdates()
    {
        $report = array(
            'required_updates' => [],
            'valid_updates'    => [],
        );
        foreach ($this->versions as $version) {
            $version->setDB($this->connection);
            if ( ! $version->verifyUpdate()) {
                $report['required_updates'][] = $version;
            } else {
                $report['valid_updates'][] = $version;
            }
        }

        return $report;
    }

    /**
     * Gets all *.php files from public $versionsDirectory
     * checks to make sure they're not in public $blackList
     * and they're a valid instance of class Version
     */
    public function getVersions()
    {
        foreach (glob($this->versionsDirectory) as $file) {
            $class = $this->getClassNamespaceFromFile($file) . "\\" . basename($file, '.php');
            if (class_exists($class) && ! in_array($class, $this->blackList)) {
                $obj = new $class($this->connection);
                if ($obj instanceof Version) {
                    $this->versions[(string)$obj->getVersion()] = $obj;
                }
            }
        }
    }


    /**
     * :^)
     * https://stackoverflow.com/questions/7153000/get-class-name-from-file
     * get the class namespace form file path using token
     *
     * @param $filePathName
     *
     * @return  null|string
     */
    protected function getClassNamespaceFromFile($filePathName)
    {
        $src = file_get_contents($filePathName);

        $tokens       = token_get_all($src);
        $count        = count($tokens);
        $i            = 0;
        $namespace    = '';
        $namespace_ok = false;
        while ($i < $count) {
            $token = $tokens[$i];
            if (is_array($token) && $token[0] === T_NAMESPACE) {
                // Found namespace declaration
                while (++$i < $count) {
                    if ($tokens[$i] === ';') {
                        $namespace_ok = true;
                        $namespace    = trim($namespace);
                        break;
                    }
                    $namespace .= is_array($tokens[$i]) ? $tokens[$i][1] : $tokens[$i];
                }
                break;
            }
            $i++;
        }
        if ( ! $namespace_ok) {
            return null;
        } else {
            return $namespace;
        }
    }


}