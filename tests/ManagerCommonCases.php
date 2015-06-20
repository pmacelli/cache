<?php namespace Comodojo\Cache\Tests;

class ManagerCommonCases extends \PHPUnit_Framework_TestCase {

    private $string_content = "Lorem ipsum dolor";

    private $array_content = array(
        'Ford'      =>  'Prefect',
        'Zaphod'    =>  'Beeblebrox',
        'Tricia'    =>  'Mc Millan',
        'Marvin'    =>  null
    );

    protected $manager = null;

    public function testSet() {

        $result = $this->manager->set("test-cache-1", $this->string_content);

        $this->assertInternalType('array', $result);

        $this->assertCount(5, $result);

        foreach ($result as $cache_result) $this->assertTrue($cache_result);

    }

    public function testGet() {

        $result = $this->manager->get("test-cache-1");

        $this->assertEquals($this->string_content, $result);

        // $status = $this->manager->status();

        // var_export($status[$this->manager->getSelectedCache()]['provider']);

    }

    public function testDelete() {

        $result = $this->manager->delete("test-cache-1");

        $this->assertInternalType('array', $result);

        $this->assertCount(5, $result);

        foreach ($result as $cache_result) $this->assertTrue($cache_result);
        
    }

    public function testSetArray() {

        $result = $this->manager->set("test-cache-2", $this->array_content);

        $this->assertInternalType('array', $result);

        $this->assertCount(5, $result);

        foreach ($result as $cache_result) $this->assertTrue($cache_result);
        
        $result = $this->manager->get("test-cache-2");

        $this->assertSame($this->array_content, $result);

        // $status = $this->manager->status();

        // var_export($status[$this->manager->getSelectedCache()]['provider']);

    }

    public function testSetExpire() {

        $result = $this->manager->set("test-cache-3", $this->string_content, 1);

        $this->assertInternalType('array', $result);

        $this->assertCount(5, $result);

        foreach ($result as $cache_result) $this->assertTrue($cache_result);
        
    }

    /**
     * @runInSeparateProcess
     */
    public function testGetExpired() {

        sleep(3);

        $result = $this->manager->setTime()->get("test-cache-3");
        
        $this->assertNull($result);

        // $status = $this->manager->status();

        // var_export($status[$this->manager->getSelectedCache()]['provider']);

    }

    public function testChangeNamespace() {

        $result = $this->manager->setNamespace('comodojo');

        $this->assertInstanceOf('\Comodojo\Cache\CacheManager', $result);

        $result = $this->manager->set("test-cache-4", $this->string_content);

        $this->assertInternalType('array', $result);

        $this->assertCount(5, $result);

        foreach ($result as $cache_result) $this->assertTrue($cache_result);
        
        $result = $this->manager->get("test-cache-4");

        $this->assertEquals($this->string_content, $result);

        $result = $this->manager->setNamespace('foonamespace');

        $this->assertInstanceOf('\Comodojo\Cache\CacheManager', $result);

        $result = $this->manager->get("test-cache-4");

        $this->assertNull($result);
        
    }

    public function testStatus() {

        $result = $this->manager->status();

        $this->assertInternalType('array', $result);

        $this->assertCount(5, $result);

    }

    /**
     * @after
     */
    public function testFlush() {

        $result = $this->manager->flush();

        $this->assertInternalType('array', $result);

        $this->assertCount(5, $result);

        foreach ($result as $cache_result) $this->assertTrue($cache_result);
        
        $this->assertNull($this->manager->get("test-cache-1"));

        $this->assertNull($this->manager->get("test-cache-2"));
        
        $this->assertNull($this->manager->get("test-cache-3"));

    }

}
