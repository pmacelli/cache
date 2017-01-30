<?php namespace Comodojo\SimpleCache\Tests\Utils;

class SimpleCacheCommonCases extends \PHPUnit_Framework_TestCase {

    protected $provider;

    protected function tearDown() {

        unset($this->provider);

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
    public function testSetGetTypes($value, $type) {

        $this->assertTrue($this->provider->set('Ford', $value));

        $item = $this->provider->get('Ford');

        $this->assertEquals($value, $item);
        $this->assertEquals($type, gettype($item));

    }

    /**
     * @expectedException \Comodojo\Exception\InvalidSimpleCacheArgumentException
     */
    public function testNullException() {

        $this->provider->set('Ford', null);

    }

    /**
     * Provides a set of test values for saving and retrieving.
     *
     * @return array
     */
    public function providerPrimitiveValues() {
        $object = new \stdClass();
        $object->name = 'Marvin';
        return [
            ['bar', 'string'],
            [1, 'integer'],
            [3.141592, 'double'],
            [['a', 'b', 'c'], 'array'],
            [['a' => 'A', 'b' => 'B', 'c' => 'C'], 'array'],
            [$object, 'object']
        ];
    }

    public function testShortTtl() {

        $this->provider->set('Marvin', 'Sad robot', 1);
        sleep(2);
        $this->assertNull($this->provider->get('Marvin'));

    }

    public function testCustomDefaultValue() {

        $this->assertEquals(42, $this->provider->get('Arthur', 42));

    }

    public function testDelete() {

        $this->provider->set('Marvin', 'Sad robot');

        $this->assertTrue($this->provider->delete('Marvin'));

        $this->assertNull($this->provider->get('Marvin'));

    }

    public function testClear() {

        $this->provider->set('Marvin', 'Sad robot');

        $this->assertTrue($this->provider->clear());

        $this->assertNull($this->provider->get('Marvin'));

    }

    public function testMultiple() {

        $key_val = [];
        $i = 0;

        foreach ($this->providerPrimitiveValues() as $set) {

            $key_val['dataset_'.$i] = $set[0];

            ++$i;

        }

        $keys = array_keys($key_val);
        $empty = array_combine($keys, array_fill(0, count($keys), null));

        $this->assertTrue($this->provider->setMultiple($key_val));

        $this->assertEquals($key_val, $this->provider->getMultiple($keys));

        $this->assertTrue($this->provider->deleteMultiple($keys));

        $this->assertEquals($empty, $this->provider->getMultiple($keys));

    }

    public function testHas() {

        $this->provider->set('Marvin', 'Sad robot');
        $this->assertTrue($this->provider->has('Marvin'));
        $this->assertFalse($this->provider->has('Marvin_1'));
        $this->assertTrue($this->provider->delete('Marvin'));
        $this->assertFalse($this->provider->has('Marvin'));

    }

    public function testChangeNamespace() {

        $value = 'I\'m changing namespace!';

        $this->assertTrue($this->provider->set('foo', $value));

        $this->provider->setNamespace('FOO');

        $this->assertTrue($this->provider->set('foo', $value));

        $this->provider->setNamespace('TEST');

        $this->assertFalse($this->provider->has('foo'));
        $this->assertNull($this->provider->get('foo'));

        $this->provider->setNamespace('FOO');

        $this->assertTrue($this->provider->has('foo'));
        $this->assertEquals($value, $this->provider->get('foo'));

        $this->assertTrue($this->provider->clearNamespace());

        $this->assertFalse($this->provider->has('foo'));
        $this->assertNull($this->provider->get('foo'));

        $this->provider->setNamespace();

        $this->assertTrue($this->provider->has('foo'));
        $this->assertEquals($value, $this->provider->get('foo'));

    }

}
