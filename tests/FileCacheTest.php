<?php namespace Comodojo\Cache\Tests;

class FileCacheTest extends CommonCases {

    protected $cache_folder = null;

    protected function setUp() {
        
        $this->cache_folder = __DIR__ . "/localcache/";    

        $this->cache = new \Comodojo\Cache\FileCache($this->cache_folder);

        parent::setUp();
    
    }

}
