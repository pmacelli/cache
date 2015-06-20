<?php

use \Comodojo\Cache\Tests\CommonCases;

class DatabaseCacheTest extends CommonCases {

    protected function setUp() {
        
    	$edb = \Comodojo\Cache\DatabaseCache::getDatabase('MYSQLI', '127.0.0.1', 3306, 'comodojo', 'root');

        $this->cache = new \Comodojo\Cache\DatabaseCache($edb, 'cache', 'comodojo_');
    
    }

    protected function tearDown() {

        unset($this->cache);

    }

}
