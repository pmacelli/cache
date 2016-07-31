<?php

use \Comodojo\Cache\Tests\CommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class MemcachedTest extends CommonCases {

    protected static $logger;

    public static function setupBeforeClass() {

        self::$logger = new Logger('MemcachedCacheTest');

        self::$logger->pushHandler(new StreamHandler(__DIR__."/../tmp/MemcachedCacheTest.log", Logger::DEBUG));

    }

    protected function setUp() {

        $this->cache = new \Comodojo\Cache\Providers\Memcached('127.0.0.1', 11211, 0, null, self::$logger);

    }

    protected function tearDown() {

        unset($this->cache);

    }

}
