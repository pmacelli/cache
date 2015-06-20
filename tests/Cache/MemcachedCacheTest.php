<?php

use \Comodojo\Cache\Tests\CommonCases;

class MemcachedCacheTest extends CommonCases {

    protected function setUp() {
        
        $this->cache = new \Comodojo\Cache\MemcachedCache('127.0.0.1');
    
    }

    protected function tearDown() {

        unset($this->cache);

    }

}
