<?php

namespace Solvers\Dsql;

use Nette\Database as Database;
use Dotenv\Dotenv;
use Nette\Database\Connection;
use Shieldon\Security\Xss;

class Application
{
    private $databaseHost;

    private $databaseName;

    private $databaseUser;

    private $databaseUserPassword;

    private $baseUrl;

    private $xss;

    private $auth;

    public function __construct()
    {
        Dotenv::createImmutable(__DIR__)->load();
        $this->databaseHost = env('DB_HOST');
        $this->databaseName = env('DB_DATABASE');
        $this->databaseUser = env('DB_USERNAME');
        $this->databaseUserPassword = env('DB_PASSWORD');
        $this->baseUrl = env('APP_URL');
        $this->xss = new Xss();
    }

    /**
     * @return mixed
     */
    public function getDb()
    {
        return self::$db;
    }

    public function base_url()
    {
        return $_ENV['APP_URL'];
    }

    function getDBMain()
    {
        return $_ENV['DB_DATABASE'];
    }

    /**
     * @return Connection
     */
    function getDBConnection(): Database\Connection
    {
        $dsn = $_ENV['DATABASE_DSN'];

        return new Database\Connection($dsn, $this->databaseUser, $this->databaseUserPassword);
    }


    public function makeURL($parentName)
    {
        return $this->baseUrl . 'index.php?parent=' . $parentName;
    }

    /**
     * @param $value
     * @return float|int|mixed|string
     */
    function cleanInput($value)
    {
        return $this->xss->clean($value);
    }

    /**
     * @return mixed
     */
    public function getDatabaseHost()
    {
        return $this->databaseHost;
    }

    /**
     * @param mixed $databaseHost
     */
    public function setDatabaseHost($databaseHost): void
    {
        $this->databaseHost = $databaseHost;
    }

    /**
     * @return mixed
     */
    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    /**
     * @param mixed $databaseName
     */
    public function setDatabaseName($databaseName): void
    {
        $this->databaseName = $databaseName;
    }

    /**
     * @return mixed
     */
    public function getDatabaseUser()
    {
        return $this->databaseUser;
    }

    /**
     * @param mixed $databaseUser
     */
    public function setDatabaseUser($databaseUser): void
    {
        $this->databaseUser = $databaseUser;
    }

    /**
     * @return mixed
     */
    public function getDatabaseUserPassword()
    {
        return $this->databaseUserPassword;
    }

    /**
     * @param mixed $databaseUserPassword
     */
    public function setDatabaseUserPassword($databaseUserPassword): void
    {
        $this->databaseUserPassword = $databaseUserPassword;
    }

    /**
     * @return mixed
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }

    /**
     * @param mixed $baseUrl
     */
    public function setBaseUrl($baseUrl): void
    {
        $this->baseUrl = $baseUrl;
    }

    public function getUserNotifications($userId, $companyId){

    }



//$dsn = "sqlsrv:server=127.0.01 ; Database=DataCollector";
//$loggedUserName = "sa";
//$password = "nopass@1234";
//
////database = new Nette\Database\Connection($dsn, $loggedUserName, $password);
//
//$storage = new Nette\Caching\Storages\FileStorage();
//$connection = new Nette\Database\Connection($dsn, $loggedUserName, $password);
//$structure = new Nette\Database\Structure($connection, $storage);
//$conventions = new Nette\Database\Conventions\DiscoveredConventions($structure);
//$explorer = new Nette\Database\Explorer($connection, $structure, $conventions, $storage);
}