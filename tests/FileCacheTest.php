<?php namespace Comodojo\Cache;

class FileCacheTest extends \PHPUnit_Framework_TestCase {

    protected $cache_folder = null;

    protected $cache_content = null;

    protected function setUp() {
        
        $this->cache_folder = __DIR__ . "/localcache/";

        $this->cache_content = "Lorem ipsum dolor";
    
    }

    public function testCacheIsConsistent() {
        
        $cache = new FileCache($this->cache_folder);

        $cache->set("test-cache", $this->cache_content);
        
        $this->assertEquals($this->cache_content, $cache->get("test-cache"));

    }

}
