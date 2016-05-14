<?php

use \Comodojo\Cache\ApcCache;
use \Comodojo\Cache\DatabaseCache;
use \Comodojo\Cache\FileCache;
use \Comodojo\Cache\MemcachedCache;
use \Comodojo\Cache\PhpRedisCache;
use \Comodojo\Cache\CacheManager;
use \Comodojo\Cache\Tests\ManagerCommonCases;

class CacheManagerPickByWeightTest extends ManagerCommonCases {

    protected function setUp() {

        $cache_folder = __DIR__ . "/../localcache/";

        $edb = DatabaseCache::getDatabase('MYSQLI', '127.0.0.1', 3306, 'comodojocache', 'root', 'comodojo');

        $this->manager = new CacheManager( CacheManager::PICK_BYWEIGHT);

        $this->manager->addProvider( new ApcCache(), 10 );
        $this->manager->addProvider( new DatabaseCache($edb, 'cache', 'cmdj_'), 20);
        $this->manager->addProvider( new FileCache($cache_folder), 30 );
        $this->manager->addProvider( new MemcachedCache('127.0.0.1'), 50 );
        $this->manager->addProvider( new PhpRedisCache('127.0.0.1'), 50 );

    }

    protected function tearDown() {

        unset($this->manager);

    }

}
