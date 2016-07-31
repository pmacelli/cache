<?php

use \Comodojo\Cache\Tests\CommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class DatabaseTest extends CommonCases {

    protected static $logger;

    public static function setupBeforeClass() {

        self::$logger = new Logger('DatabaseTest');

        self::$logger->pushHandler(new StreamHandler(__DIR__."/../tmp/DatabaseTest.log", Logger::DEBUG));

    }

    protected function setUp() {

        $edb = \Comodojo\Cache\DatabaseCache::getDatabase('MYSQLI', '127.0.0.1', 3306, 'comodojocache', 'root', 'comodojo');

        $this->cache = new \Comodojo\Cache\Providers\Database($edb, 'cache', 'cmdj_', self::$logger);

    }

    protected function tearDown() {

        unset($this->cache);

    }

}
