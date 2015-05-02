<?php namespace Comodojo\Cache\Tests;

class CommonCases extends \PHPUnit_Framework_TestCase {

    protected $cache_content = null;

    protected $cache = null;

    protected function setUp() {
        
        $this->cache_content = "Lorem ipsum dolor";
    
    }

    public function testCacheIsConsistent() {
        
        $this->cache->set("test-cache", $this->cache_content);
        
        $this->assertEquals($this->cache_content, $this->cache->get("test-cache"));

    }

    public function testCacheStatus() {
        
        $result = $this->cache->status();
        
        $this->assertArrayHasKey("online", $result);
        $this->assertArrayHasKey("objects", $result);
        $this->assertArrayHasKey("options", $result);

    }

    public function testFlushCacheByName() {
        
        $result = $this->cache->flush("test-cache");
        
        $this->assertTrue($result);

    }

}
