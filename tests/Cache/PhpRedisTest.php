<?php

use \Comodojo\Cache\Tests\CommonCases;

class PhpRedisCacheTest extends CommonCases {

    protected function setUp() {
        
        $this->cache = new \Comodojo\Cache\PhpRedisCache('127.0.0.1');
    
    }

    protected function tearDown() {

        unset($this->cache);

    }

}
