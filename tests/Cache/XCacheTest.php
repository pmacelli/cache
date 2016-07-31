<?php

use \Comodojo\Cache\Tests\CommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class XCacheTest extends CommonCases {

    protected static $logger;

    public static function setupBeforeClass() {

        self::$logger = new Logger('XCacheCacheTest');

        self::$logger->pushHandler(new StreamHandler(__DIR__."/../tmp/XCacheCacheTest.log", Logger::DEBUG));

    }

    protected function setUp() {

        $this->cache = new \Comodojo\Cache\Providers\XCache(self::$logger);

    }

    protected function tearDown() {

        unset($this->cache);

    }

}
