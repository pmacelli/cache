<?php namespace Comodojo\Cache\Tests;

use \Comodojo\Cache\Item;
use \DateTime;

class ItemTest extends \PHPUnit_Framework_TestCase {

    public function testNewItem() {

        $key = 'Marvin';

        $value = 'Time is an illusion. Lunchtime doubly so';

        $expire = new DateTime('now + 10 minutes');

        $item = new Item($key);

        $this->assertInstanceOf('\Comodojo\Cache\Item', $item);

        $this->assertEquals($key, $item->getKey());

        $this->assertNull($item->get());

        $this->assertFalse($item->isHit());

        $this->assertInstanceOf('\Comodojo\Cache\Item', $item->set($value)->expiresAt($expire));

        $this->assertEquals($expire, $item->getExpiration());

        $this->assertEquals((int)date_create('now')->diff($expire)->format("%s"), $item->getTtl());

        $this->assertInstanceOf('\Comodojo\Cache\Item', $item->expiresAfter(2));

        sleep(3);

        $this->assertLessThan(0, $item->getTtl());

    }

    public function testRetrieved() {

        $key = 'Marvin';

        $value = 'Time is an illusion. Lunchtime doubly so';

        $item = new Item($key, true);

        $item->set($value);

        $this->assertInstanceOf('\Comodojo\Cache\Item', $item);

        $this->assertEquals($key, $item->getKey());

        $this->assertEquals($value, $item->get());

        $this->assertTrue($item->isHit());

    }

    /**
     * Verifies key's name in positive cases.
     *
     * @param string $key
     *   The key's name.
     *
     * @dataProvider providerValidKeyNames
     */
    public function testPositiveValidateKey($key) {

        $this->assertInstanceOf('\Comodojo\Cache\Item', new Item($key));

    }

    /**
     * @expectedException \Comodojo\Exception\InvalidCacheArgumentException
     * @dataProvider providerNotValidKeyNames
     */
    public function testInvalidKey() {

        $item = new Item('{sdsdsdsd}');

    }

    /**
     * Provides a set of valid test key names.
     *
     * @return array
     */
    public function providerValidKeyNames() {
        return [
            ['bar'],
            ['barFoo1234567890'],
            ['bar_Foo.1'],
            ['1'],
            [str_repeat('a', 64)]
        ];
    }

    /**
     * Provides a set of not valid test key names.
     *
     * @return array
     */
    public function providerNotValidKeyNames() {
        return [
            [null],
            [1],
            [''],
            ['bar{Foo'],
            ['bar}Foo'],
            ['bar(Foo'],
            ['bar)Foo'],
            ['bar/Foo'],
            ['bar\Foo'],
            ['bar@Foo'],
            ['bar:Foo']
        ];
    }

    public function testGetRaw() {

        $value = 'this is a test';

        $item = new Item('test', false);

        $item->set($value);

        $this->assertNull($item->get());

        $this->assertEquals($value, $item->getRaw());

    }

}
