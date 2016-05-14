<?php

use \Comodojo\Cache\ApcCache;
use \Comodojo\Cache\DatabaseCache;
use \Comodojo\Cache\FileCache;
use \Comodojo\Cache\MemcachedCache;
use \Comodojo\Cache\PhpRedisCache;
use \Comodojo\Cache\CacheManager;
use \Comodojo\Cache\Tests\ManagerCommonCases;

class CacheManagerPickRandomTest extends ManagerCommonCases {

    protected function setUp() {

        $cache_folder = __DIR__ . "/../localcache/";

        $edb = DatabaseCache::getDatabase('MYSQLI', '127.0.0.1', 3306, 'comodojocache', 'root', 'comodojo');

        $this->manager = new CacheManager(CacheManager::PICK_RANDOM);

        $this->manager->addProvider( new ApcCache() );
        $this->manager->addProvider( new DatabaseCache($edb, 'cache', 'cmdj_') );
        $this->manager->addProvider( new FileCache($cache_folder) );
        $this->manager->addProvider( new MemcachedCache('127.0.0.1') );
        $this->manager->addProvider( new PhpRedisCache('127.0.0.1') );

    }

    protected function tearDown() {

        unset($this->manager);

    }

}
