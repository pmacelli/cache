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

        $edb = DatabaseCache::getDatabase('MYSQLI', '127.0.0.1', 3306, 'comodojo', 'root');

        $this->manager = new CacheManager( CacheManager::PICK_RANDOM);

        $this->manager->add( new ApcCache() );
        $this->manager->add( new DatabaseCache($edb, 'cache', 'comodojo_') );
        $this->manager->add( new FileCache($cache_folder) );
        $this->manager->add( new MemcachedCache('127.0.0.1') );
        $this->manager->add( new PhpRedisCache('127.0.0.1') );
    
    }

    protected function tearDown() {

        unset($this->manager);

    }

}
