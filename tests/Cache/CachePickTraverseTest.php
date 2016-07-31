<?php

use \Comodojo\Cache\Providers\Apc;
use \Comodojo\Cache\Providers\Filesystem;
use \Comodojo\Cache\Providers\Memcached;
use \Comodojo\Cache\Providers\PhpRedis;
use \Comodojo\Cache\Cache;
use \Comodojo\Cache\Tests\ManagerCommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class CachePickTraverseTest extends ManagerCommonCases {

    public static $cache;

    public static $id;

    public static function setupBeforeClass() {

        $logger = new Logger('CachePickTraverseTest');

        $logger->pushHandler(new StreamHandler(__DIR__."/../tmp/CachePickTraverseTest.log", Logger::DEBUG));

        $cache_folder = __DIR__ . "/../localcache/";

        self::$cache = new Cache(Cache::PICK_TRAVERSE, $logger, 3600, 5);

        self::$cache->setAutoSetTime();

        $provider = new Filesystem($cache_folder);

        self::$id = $provider->getCacheId();

        self::$cache->addProvider( $provider );

    }

    public function testOrder() {

        $cache = self::$cache;

        $cache->set("test-cache-0", "this is a test");

        $value = $cache->get("test-cache-0");

        $this->assertEquals("this is a test", $value);

        $cache->removeProvider(self::$id);

        $cache_folder = __DIR__ . "/../localcache/";

        $cache->addProvider( new Apc() );
        $cache->addProvider( new Memcached('127.0.0.1') );
        $cache->addProvider( new PhpRedis('127.0.0.1') );
        $cache->addProvider( new Filesystem($cache_folder) );

        $value = $cache->get("test-cache-0");

        $this->assertEquals("this is a test", $value);

    }

}
