<?php namespace Comodojo\Cache\Tests;

use \Comodojo\Cache\Components\NullLogger;

class CommonCases extends \PHPUnit_Framework_TestCase {

    private $string_content = "Lorem ipsum dolor";

    private $array_content = array(
        'Ford'      =>  'Prefect',
        'Zaphod'    =>  'Beeblebrox',
        'Tricia'    =>  'Mc Millan',
        'Marvin'    =>  null
    );

    protected $cache;

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

    public function testExpire() {

        $result = $this->cache->set("test-cache-3", $this->string_content, 1);

        $this->assertTrue($result);

        $this->assertFalse($this->cache->getErrorState());

        // echo "\nPreCacheTime: ".$this->cache->getTime()."\n";

        sleep(3);

        // FileProvider & DatabaseProvider relay on local script time, so let's update it.
        // this has no effect on other providers.
        $time = $this->cache->setTime()->getTime();

        //echo "\nPreCacheTime: $time\n";

        $result = $this->cache->get("test-cache-3");

        $this->assertNull($result);

        $this->assertFalse($this->cache->getErrorState());

    }

    // /**
    //  * @runInSeparateProcess
    //  */
    // public function testGetExpired() {
    //
    //     sleep(3);
    //
    //     $time = $this->cache->setTime()->getTime();
    //     $result = $this->cache->get("test-cache-3");
    //     $this->assertNull($result);
    //     $this->assertFalse($this->cache->getErrorState());
    //
    // }

    public function testChangeNamespace() {

        $result = $this->cache->setNamespace('comodojo');

        $this->assertInstanceOf('\Comodojo\Cache\Providers\ProviderInterface', $result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->set("test-cache-4", $this->string_content);

        $this->assertTrue($result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->get("test-cache-4");

        $this->assertEquals($this->string_content, $result);

        $this->assertFalse($this->cache->getErrorState());

        $result = $this->cache->setNamespace('foonamespace');

        $this->assertInstanceOf('\Comodojo\Cache\Providers\ProviderInterface', $result);

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

    public function testFlush() {

        $result = $this->cache->flush();

        $this->assertTrue($result);

        $this->assertNull($this->cache->get("test-cache-1"));

        $this->assertNull($this->cache->get("test-cache-2"));

        $this->assertNull($this->cache->get("test-cache-3"));

        $this->assertFalse($this->cache->getErrorState());

    }

    public function testLogger() {

        $className = join('', array_slice(explode('\\', get_called_class()), -1));

        $log = new NullLogger();

        $this->cache->setLogger($log);

        $logger = $this->cache->getLogger();

        $this->assertInstanceOf('\Psr\Log\LoggerInterface', $logger);

    }

    public function testSetGetTime() {

        $time = time();

        $result = $this->cache->setTime($time);

        $this->assertInstanceOf('\Comodojo\Cache\Providers\ProviderInterface', $result);

        $result = $this->cache->getTime();

        $this->assertEquals($time, $result);

    }

    public function testSetGetTtl() {

        $result = $this->cache->setTtl(300);

        $this->assertInstanceOf('\Comodojo\Cache\Providers\ProviderInterface', $result);

        $result = $this->cache->getTtl();

        $this->assertEquals(300, $result);

    }

    public function testSetGetNamespace() {

        $result = $this->cache->setNamespace('BOO');

        $this->assertInstanceOf('\Comodojo\Cache\Providers\ProviderInterface', $result);

        $result = $this->cache->getNamespace();

        $this->assertEquals('BOO', $result);

    }

    public function testEnableDisableCache() {

        $result = $this->cache->disable();

        $this->assertFalse($this->cache->isEnabled());

        $result = $this->cache->enable();

        $this->assertTrue($this->cache->isEnabled());

    }

    public function testErrorState() {

        $result = $this->cache->setErrorState();

        $this->assertTrue($this->cache->getErrorState());

        $result = $this->cache->resetErrorState();

        $this->assertFalse($this->cache->getErrorState());

    }

    /**
     * @expectedException        Comodojo\Exception\CacheException
     */
    public function testSetTimeException() {

        $this->cache->setTime('time');

    }

}
