<?php namespace Comodojo\Cache\Tests;

class CommonCases extends \PHPUnit_Framework_TestCase {

    private $string_content = "Lorem ipsum dolor";

    private $array_content = array(
        'Ford'      =>  'Prefect',
        'Zaphod'    =>  'Beeblebrox',
        'Tricia'    =>  'Mc Millan',
        'Marvin'    =>  null
    );

    protected $cache = null;

    public function testGetCacheId() {

        $result = $this->cache->getCacheId();

        $this->assertNotEmpty($result);

    }

    public function testSet() {

        $result = $this->cache->set("test-cache-1", $this->string_content);

        $this->assertTrue($result);

        $this->assertFalse($this->cache->getErrorState());

    }

    public function testGet() {

        $result = $this->cache->get("test-cache-1");

        $this->assertEquals($this->string_content, $result);

        $this->assertFalse($this->cache->getErrorState());

    }

    public function testDelete() {

        $result = $this->cache->delete("test-cache-1");

        $this->assertTrue($result);

        $this->assertFalse($this->cache->getErrorState());

    }

    public function testSetArray() {

        $result = $this->cache->set("test-cache-2", $this->array_content);

        $this->assertTrue($result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->get("test-cache-2");

        $this->assertSame($this->array_content, $result);

        $this->assertFalse($this->cache->getErrorState());

    }

    public function testSetExpire() {

        $result = $this->cache->set("test-cache-3", $this->string_content, 1);

        $this->assertTrue($result);

        $this->assertFalse($this->cache->getErrorState());

    }

    /**
     * @runInSeparateProcess
     */
    public function testGetExpired() {

        sleep(3);

        $result = $this->cache->setTime()->get("test-cache-3");
        
        $this->assertNull($result);

        $this->assertFalse($this->cache->getErrorState());

    }

    public function testChangeNamespace() {

        $result = $this->cache->setNamespace('comodojo');

        $this->assertInstanceOf('\Comodojo\Cache\CacheInterface\CacheInterface', $result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->set("test-cache-4", $this->string_content);

        $this->assertTrue($result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->get("test-cache-4");

        $this->assertEquals($this->string_content, $result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->setNamespace('foonamespace');

        $this->assertInstanceOf('\Comodojo\Cache\CacheInterface\CacheInterface', $result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->get("test-cache-4");

        $this->assertNull($result);

        $this->assertFalse($this->cache->getErrorState());
        
    }

    public function testStatus() {

        $result = $this->cache->status();

        $this->assertInternalType('array', $result);

        $this->assertArrayHasKey("provider", $result);

        $this->assertArrayHasKey("enabled", $result);

        $this->assertArrayHasKey("objects", $result);

        $this->assertArrayHasKey("options", $result);

        $this->assertFalse($this->cache->getErrorState());

    }

    /**
     * @after
     */
    public function testFlush() {

        $result = $this->cache->flush();

        $this->assertTrue($result);

        $this->assertNull($this->cache->get("test-cache-1"));

        $this->assertNull($this->cache->get("test-cache-2"));
        
        $this->assertNull($this->cache->get("test-cache-3"));

        $this->assertFalse($this->cache->getErrorState());

    }

}
