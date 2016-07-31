<?php namespace Comodojo\Cache\Tests;

class ManagerCommonCases extends \PHPUnit_Framework_TestCase {

    private $string_content = "Lorem ipsum dolor";

    private $array_content = array(
        'Ford'      =>  'Prefect',
        'Zaphod'    =>  'Beeblebrox',
        'Tricia'    =>  'Mc Millan',
        'Marvin'    =>  null
    );

    public function testSet() {

        $result = static::$cache->set("test-cache-1", $this->string_content);

        $this->assertTrue($result);

    }

    public function testGet() {

        $result = static::$cache->get("test-cache-1");

        $this->assertEquals($this->string_content, $result);

    }

    public function testDelete() {

        $result = static::$cache->delete("test-cache-1");

        $this->assertTrue($result);

        $result = static::$cache->get("test-cache-1");

        $this->assertNull($result);

    }

    public function testSetArray() {

        $result = static::$cache->set("test-cache-2", $this->array_content);

        $this->assertTrue($result);

        $result = static::$cache->get("test-cache-2");

        $this->assertSame($this->array_content, $result);

    }

    public function testSetExpire() {

        $result = static::$cache->set("test-cache-3", $this->string_content, 1);

        $this->assertTrue($result);

        sleep(3);

        // static::$cache->setTime();

        $result = static::$cache->get("test-cache-3");

        $this->assertNull($result);

    }

    public function testChangeNamespace() {

        $result = static::$cache->setNamespace('comodojo');

        $this->assertInstanceOf('\Comodojo\Cache\Cache', $result);

        $result = static::$cache->set("test-cache-4", $this->string_content);

        $this->assertTrue($result);

        $result = static::$cache->get("test-cache-4");

        $this->assertEquals($this->string_content, $result);

        $result = static::$cache->setNamespace('foonamespace')->get("test-cache-4");

        $this->assertNull($result);

        static::$cache->setNamespace();

    }

    public function testStatus() {

        $result = static::$cache->status();

        $this->assertInternalType('array', $result);

        $this->assertCount(4, $result);

    }

    public function testFlush() {

        $result = static::$cache->flush();

        $this->assertTrue($result);

        $this->assertNull(static::$cache->get("test-cache-1"));

        $this->assertNull(static::$cache->get("test-cache-2"));

        $this->assertNull(static::$cache->get("test-cache-3"));

        $this->assertNull(static::$cache->setNamespace('comodojo')->get("test-cache-4"));

    }

}
