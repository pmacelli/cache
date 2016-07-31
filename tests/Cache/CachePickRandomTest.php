<?php

use \Comodojo\Cache\Providers\Apc;
use \Comodojo\Cache\Providers\Filesystem;
use \Comodojo\Cache\Providers\Memcached;
use \Comodojo\Cache\Providers\PhpRedis;
use \Comodojo\Cache\Cache;
use \Comodojo\Cache\Tests\ManagerCommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class CachePickRandomTest extends ManagerCommonCases {

    public static $cache;

    public static function setupBeforeClass() {

        $logger = new Logger('CachePickRandomTest');

        $logger->pushHandler(new StreamHandler(__DIR__."/../tmp/CachePickRandomTest.log", Logger::DEBUG));

        $cache_folder = __DIR__ . "/../localcache/";

        self::$cache = new Cache(Cache::PICK_RANDOM, $logger, 3600, 5);

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

        $cache->set("test-cache-0", "this is a test");

        $sids = array();

        for ($i=0; $i < 30; $i++) {

            $cache->get("test-cache-0");

            $sid = $cache->getSelectedProvider()->getCacheId();

            $cache->getLogger()->info("Selected provider $sid");

            $sids[] = $sid;

        }

        $used_providers = array_unique($sids);

        $cache->getLogger()->info("Unique sort of used providers", $used_providers);

        $this->assertGreaterThan(1, count($used_providers));

        $cache->delete("test-cache-0");

    }

}
