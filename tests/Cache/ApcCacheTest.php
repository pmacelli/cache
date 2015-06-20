<?php

use \Comodojo\Cache\Tests\CommonCases;

class ApcCacheTest extends CommonCases {

    protected function setUp() {
        
        $this->cache = new \Comodojo\Cache\ApcCache();
    
    }

    protected function tearDown() {

        unset($this->cache);

    }

}
