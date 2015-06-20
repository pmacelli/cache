<?php

use \Comodojo\Cache\Tests\CommonCases;

class XCacheCacheTest extends CommonCases {

    protected function setUp() {
        
        $this->cache = new \Comodojo\Cache\XCacheCache();
    
    }

    protected function tearDown() {

        unset($this->cache);

    }

}
