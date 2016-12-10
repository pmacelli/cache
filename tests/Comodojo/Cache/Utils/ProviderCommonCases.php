<?php namespace Comodojo\Cache\Tests\Utils;

use \Comodojo\Cache\Item;
use \DateTime;

class ProviderCommonCases extends \PHPUnit_Framework_TestCase {

    protected $pool;

    public function testGetId() {

        $result = $this->pool->getId();

        $this->assertNotEmpty($result);

    }

    public function testChangeState() {

        $message = 'Marvin seems sad tonight';

        $class = get_class($this->pool);

        $this->assertEquals(0, $this->pool->getState());

        $this->assertNull($this->pool->getStateMessage());

        $time = new DateTime('now');

        $this->pool->setState(1, $message);

        $this->assertEquals(1, $this->pool->getState());

        $this->assertEquals($message, $this->pool->getStateMessage());

        $this->assertGreaterThanOrEqual($time, $this->pool->getStateTime());

    }

    /**
     * Verifies that a cache miss returns NULL.
     */
    public function testEmptyItem() {
        $this->assertFalse($this->pool->hasItem('Ford'));
        $item = $this->pool->getItem('Ford');
        $this->assertNull($item->get());
        $this->assertFalse($item->isHit());
    }

    /**
     * Verifies that primitive items can be added and retrieved from the pool.
     *
     * @param mixed $value
     *   A value to try and cache.
     * @param string $type
     *   The type of variable we expect to be cached.
     *
     * @dataProvider providerPrimitiveValues
     */
    public function testAddItem($value, $type) {
        $item = $this->pool->getItem('Ford');
        $item->set($value);
        $this->pool->save($item);

        $item = $this->pool->getItem('Ford');
        $this->assertEquals($value, $item->get());
        $this->assertEquals($type, gettype($item->get()));
    }

    public function testAddGetDeleteItems() {

        $item_1 = $this->pool->getItem('Ford');
        $item_2 = $this->pool->getItem('Marvin');

        $item_1->set('ford')->expiresAfter(100);
        $item_1->set('marvin')->expiresAfter(100);

        $this->pool->saveDeferred($item_1);
        $this->pool->saveDeferred($item_2);

        $this->assertTrue($this->pool->commit());

        $this->assertTrue($this->pool->hasItem('Ford'));
        $this->assertTrue($this->pool->hasItem('Marvin'));

        foreach ($this->pool->getItems(['Ford', 'Marvin']) as $item) {

            $this->assertTrue($item->isHit());

        }

        $this->assertTrue($this->pool->deleteItems(['Ford','Marvin']));

        $this->assertFalse($this->pool->hasItem('Ford'));
        $this->assertFalse($this->pool->hasItem('Marvin'));

    }

    public function testDeleteItem() {

        $item = $this->pool->getItem('Ford');
        $item->set('I\'m perfect!');
        $this->pool->save($item);

        $this->assertTrue($this->pool->deleteItem('Ford'));

        $this->assertFalse($this->pool->deleteItem('Ford'));

    }

    public function testClear() {

        $item = $this->pool->getItem('Perfect');
        $item->set('I\'m Ford!');
        $this->pool->save($item);

        $this->pool->clear();

        $item = $this->pool->getItem('Perfect');

        $this->assertFalse($item->isHit());
        $this->assertNull($item->get());

    }

    /**
     * Provides a set of test values for saving and retrieving.
     *
     * @return array
     */
    public function providerPrimitiveValues() {
        return [
            ['bar', 'string'],
            [1, 'integer'],
            [3.141592, 'double'],
            [['a', 'b', 'c'], 'array'],
            [['a' => 'A', 'b' => 'B', 'c' => 'C'], 'array'],
        ];
    }

    /**
     * Verifies that an item with an expiration time in the past won't be retrieved.
     *
     * @param mixed $value
     *   A value to try and cache.
     *
     * @dataProvider providerPrimitiveValues
     */
    public function testExpiresAt($value) {

        $item = $this->pool->getItem('foo');
        $item->set($value)
            ->expiresAt(new DateTime('-1 minute'));
        $this->pool->save($item);

        $item = $this->pool->getItem('foo');
        $this->assertNull($item->get());
        $this->assertFalse($item->isHit());
    }

    public function testChangeNamespace() {

        $value = 'I\'m changing namespace!';

        $item = $this->pool->getItem('foo');
        $item->set($value)
            ->expiresAt(new DateTime('+10 minutes'));
        $this->pool->save($item);

        $this->pool->setNamespace('TEST');

        $item = $this->pool->getItem('foo');
        $this->assertNull($item->get());
        $this->assertFalse($item->isHit());

        $item = $this->pool->getItem('foo');
        $item->set($value)
            ->expiresAt(new DateTime('+10 minutes'));
        $this->pool->save($item);

        $item = $this->pool->getItem('foo');
        $this->assertEquals($value, $item->get());
        $this->assertTrue($item->isHit());

    }

    public function testDeferred() {

        $key = 'Marvin';

        $value = 'So sad...';

        $item = $this->pool->getItem($key);

        $item->set($value)->expiresAfter(100);

        $this->assertTrue($this->pool->saveDeferred($item));

        $this->assertFalse($this->pool->hasItem($key));

        $this->assertTrue($this->pool->commit());

        $this->assertTrue($this->pool->hasItem($key));

        $this->assertEquals($value, $this->pool->getItem($key)->get());

    }

    public function testStatus() {

        $status = $this->pool->getStatus();

        $this->assertInstanceOf('\Comodojo\Cache\Components\StatefulCacheItemPoolStatus', $status);

    }

}
