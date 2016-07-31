<?php

use \Comodojo\Cache\Tests\CommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class FilesystemTest extends CommonCases {

    protected static $logger;

    public static function setupBeforeClass() {

        self::$logger = new Logger('FileCacheTest');

        self::$logger->pushHandler(new StreamHandler(__DIR__."/../tmp/FileCacheTest.log", Logger::DEBUG));

    }

    protected function setUp() {

        $cache_folder = __DIR__ . "/../localcache/";

        $this->cache = new \Comodojo\Cache\Providers\Filesystem($cache_folder, self::$logger);

    }

    protected function tearDown() {

        unset($this->cache);

    }

}
