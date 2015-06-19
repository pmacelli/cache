<?php

use \Comodojo\Cache\Tests\CommonCases;

class FileCacheTest extends CommonCases {

    protected function setUp() {
        
        $cache_folder = __DIR__ . "/../localcache/";    

        $this->cache = new \Comodojo\Cache\FileCache($cache_folder);
    
    }

}
