<?php

use \Comodojo\Cache\Providers\Apc;
use \Comodojo\Cache\Providers\Filesystem;
use \Comodojo\Cache\Providers\Memcached;
use \Comodojo\Cache\Providers\PhpRedis;
use \Comodojo\Cache\Cache;
use \Comodojo\Cache\Tests\ManagerCommonCases;
use \Monolog\Logger;
use \Monolog\Handler\StreamHandler;

class CachePickAllTest extends ManagerCommonCases {

    public static $cache;

    public static function setupBeforeClass() {

        $logger = new Logger('CachePickAllTest');

        $logger->pushHandler(new StreamHandler(__DIR__."/../tmp/CachePickAllTest.log", Logger::DEBUG));

        $cache_folder = __DIR__ . "/../localcache/";

        self::$cache = new Cache(Cache::PICK_LAST, $logger, 3600, 5);

        self::$cache->setAutoSetTime();

        self::$cache->addProvider( new Apc() );
        //self::$cache->addProvider( new DatabaseCache($edb, 'cache', 'cmdj_') );
        self::$cache->addProvider( new Filesystem($cache_folder) );
        self::$cache->addProvider( new Memcached('127.0.0.1') );
        self::$cache->addProvider( new PhpRedis('127.0.0.1') );

    }

}
