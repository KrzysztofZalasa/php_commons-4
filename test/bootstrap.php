<?php

/**
 * =============================================================================
 * @file       bootstrap.php
 * @author     Lukasz Cepowski <lukasz@cepowski.com>
 * 
 * @copyright  PHP Commons
 *             Copyright (C) 2009-2013 PHP Commons Contributors
 *             All rights reserved.
 *             www.phpcommons.com
 * =============================================================================
 */

$rootDirPath = dirname(dirname(__FILE__));
defined('ROOT_PATH') || define('ROOT_PATH', $rootDirPath);

date_default_timezone_set('UTC');
error_reporting(E_ALL);
ini_set('display_startup_errors', 1);
ini_set('display_errors', 1);

$config = array(
    'DATABASE_DRIVER'	=> 'mysql',
    'DATABASE_HOST'		=> 'localhost',
    'DATABASE_PORT'		=> 3306,
    'DATABASE_USERNAME' => 'root',
    'DATABASE_PASSWORD' => ''
);

/*
 * Continous Integration.
 */
$ciConfig = strtolower(getenv('CONFIG'));
$dbHost = 'test-'.$ciConfig;
if (strstr($ciConfig, 'mysql')) {
    $config['DATABASE_DRIVER']    = 'mysql';
    $config['DATABASE_HOST']      = $dbHost;
    $config['DATABASE_PORT']      = 3306;
    $config['DATABASE_USERNAME']  = 'root';
    $config['DATABASE_PASSWORD']  = '';
} else if (strstr($ciConfig, 'postgresql')) {
    $config['DATABASE_DRIVER']    = 'pgsql';
    $config['DATABASE_HOST']      = $dbHost;
    $config['DATABASE_PORT']      = 5432;
    $config['DATABASE_USERNAME']  = 'postgres';
    $config['DATABASE_PASSWORD']  = 'postgres';
}

foreach ($config as $name => $value) {
    if (!defined($name)) {
        define($name, $value);
    }
}

require_once $rootDirPath.'/vendor/autoload.php';
//Commons\Autoloader\DefaultAutoloader::addIncludePath($rootDirPath.'/test');

$logger = new Commons\Log\Logger;
$logger->addWriter(new Commons\Log\Writer\SyslogWriter());
Commons\Log\Log::setLogger($logger);

class Bootstrap
{

    protected function __construct() {}

    public static function getDatabaseOptions($database = null)
    {
        return array(
            'database'	=> $database,
            'driver'	=> DATABASE_DRIVER,
            'host'	    => DATABASE_HOST,
            'port'		=> DATABASE_PORT,
            'username'  => DATABASE_USERNAME,
            'password'  => DATABASE_PASSWORD
        );
    }
    
    public static function executeSqlQuery($query, $database = null)
    {
        $conn = new \Commons\Sql\Connection\Connection(new \Commons\Sql\Driver\PdoDriver());
        $conn->connect(self::getDatabaseOptions($database));
        $conn->prepareStatement($query)->execute();
        $conn->disconnect();
    }

    public static function createDatabase()
    {
        $database = 'test_'.\Commons\Utils\RandomUtils::randomString(32);
        self::executeSqlQuery("CREATE DATABASE {$database}");
        return $database;
    }

    public static function dropDatabase($database)
    {
        self::executeSqlQuery("DROP DATABASE IF EXISTS {$database}");
    }

    public static function abort(\Exception $e = null)
    {
        \Commons\Utils\TestUtils::abort($e);
    }

}
