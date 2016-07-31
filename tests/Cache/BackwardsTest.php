<?php

use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class BackwardsTest extends \PHPUnit_Framework_TestCase {

    protected static $logger;

    public static function setupBeforeClass() {

        self::$logger = new Logger('BackwardsTest');

        self::$logger->pushHandler(new StreamHandler(__DIR__."/../tmp/BackwardsTest.log", Logger::DEBUG));

    }

    public function testFilesystem() {

        $cache_folder = __DIR__ . "/../localcache/";

        $cache = new \Comodojo\Cache\FileCache($cache_folder, self::$logger);

        $this->assertInstanceOf('\Comodojo\Cache\Providers\Filesystem', $cache);

    }

    public function testApc() {

        $cache = new \Comodojo\Cache\ApcCache(self::$logger);

        $this->assertInstanceOf('\Comodojo\Cache\Providers\Apc', $cache);

    }

    public function testMemcached() {

        $cache_folder = __DIR__ . "/../localcache/";

        $cache = new \Comodojo\Cache\MemcachedCache('127.0.0.1', 11211, 0, null, self::$logger);

        $this->assertInstanceOf('\Comodojo\Cache\Providers\Memcached', $cache);

    }

    public function testPhpRedis() {

        $cache_folder = __DIR__ . "/../localcache/";

        $cache = new \Comodojo\Cache\PhpRedisCache('127.0.0.1', 6379, 0, self::$logger);

        $this->assertInstanceOf('\Comodojo\Cache\Providers\PhpRedis', $cache);

    }

}
