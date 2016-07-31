<?php

use \Comodojo\Cache\Providers\Apc;
use \Comodojo\Cache\Providers\Filesystem;
use \Comodojo\Cache\Providers\Memcached;
use \Comodojo\Cache\Providers\PhpRedis;
use \Comodojo\Cache\Cache;
use \Comodojo\Cache\Tests\ManagerCommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class CachePickFirstTest extends ManagerCommonCases {

    public static $cache;

    public static function setupBeforeClass() {

        $logger = new Logger('CachePickFirstTest');

        $logger->pushHandler(new StreamHandler(__DIR__."/../tmp/CachePickFirstTest.log", Logger::DEBUG));

        $cache_folder = __DIR__ . "/../localcache/";

        self::$cache = new Cache(Cache::PICK_FIRST, $logger, 3600, 5);

        self::$cache->addProvider( new Apc() );
        //self::$cache->addProvider( new DatabaseCache($edb, 'cache', 'cmdj_') );
        self::$cache->addProvider( new Filesystem($cache_folder) );
        self::$cache->addProvider( new Memcached('127.0.0.1') );
        self::$cache->addProvider( new PhpRedis('127.0.0.1') );

    }

    public function testOrder() {

        $cache = self::$cache;

        $providers = $cache->getProviders();

        $cache->getLogger()->info("Available providers",array_keys($providers));

        reset($providers);

        $provider = current($providers);

        $id = $provider->getCacheId();

        $cache->getLogger()->info("Provider that should be selected $id");
        
        $cache->set("test-cache-0", "this is a test");

        $cache->get("test-cache-0");

        $sid = $cache->getSelectedProvider()->getCacheId();

        $cache->getLogger()->info("Selected provider $sid");

        $this->assertSame($id, $sid);

        $cache->delete("test-cache-0");

    }

}
