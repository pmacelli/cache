<?php

use \Comodojo\Cache\Tests\CommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class ApcTest extends CommonCases {

    protected static $logger;

    public static function setupBeforeClass() {

        self::$logger = new Logger('ApcCacheTest');

        self::$logger->pushHandler(new StreamHandler(__DIR__."/../tmp/ApcCacheTest.log", Logger::DEBUG));

    }

    protected function setUp() {

        $this->cache = new \Comodojo\Cache\Providers\Apc(self::$logger);

    }

    protected function tearDown() {

        unset($this->cache);

    }

}
