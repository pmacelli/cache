<?php

use \Comodojo\Cache\Providers\Apc;
use \Comodojo\Cache\Providers\Filesystem;
use \Comodojo\Cache\Providers\Memcached;
use \Comodojo\Cache\Providers\PhpRedis;
use \Comodojo\Cache\Cache;
use \Comodojo\Cache\Tests\ManagerCommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class CachePickByWeightTest extends ManagerCommonCases {

    public static $cache;

    public static function setupBeforeClass() {

        $logger = new Logger('CachePickByWeightTest');

        $logger->pushHandler(new StreamHandler(__DIR__."/../tmp/CachePickByWeightTest.log", Logger::DEBUG));

        $cache_folder = __DIR__ . "/../localcache/";

        self::$cache = new Cache(Cache::PICK_BYWEIGHT, $logger, 3600, 5);

        self::$cache->addProvider( new Apc(), 10 );
        //self::$cache->addProvider( new DatabaseCache($edb, 'cache', 'cmdj_') );
        self::$cache->addProvider( new Filesystem($cache_folder), 20 );
        self::$cache->addProvider( new Memcached('127.0.0.1'), 30 );
        self::$cache->addProvider( new PhpRedis('127.0.0.1'), 40 );

    }

    public function testOrder() {

        $cache = self::$cache;

        $cache->set("test-cache-0", "this is a test");

        $cache->get("test-cache-0");

        $initial_provider = $cache->getSelectedProvider();

        $type = $initial_provider->getType();

        $this->assertSame('Comodojo\Cache\Providers\PhpRedis', $type);

        $initial_provider->disable();

        $cache->get("test-cache-0");

        $other_provider = $cache->getSelectedProvider();

        $type = $other_provider->getType();

        $this->assertSame('Comodojo\Cache\Providers\Memcached', $type);

        $initial_provider->enable();

        $cache->get("test-cache-0");

        $back_provider = $cache->getSelectedProvider();

        $type = $back_provider->getType();

        $this->assertSame('Comodojo\Cache\Providers\PhpRedis', $type);

        $cache->delete("test-cache-0");

    }

}
