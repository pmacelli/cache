<?php namespace Comodojo\Cache\Tests\Utils;

use \Comodojo\Cache\Item;
use \DateTime;

class EnhancedProviderCommonCases extends ProviderCommonCases {

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

    public function testSimulatedFailure() {

        $status = $this->pool::CACHE_ERROR;

        $message = 'this is a simulated failure';

        $this->pool->setState($status, $message);

        $this->assertEquals($status, $this->pool->getState());
        $this->assertEquals($message, $this->pool->getStateMessage());
        $this->assertInstanceOf('\DateTimeInterface', $this->pool->getStateTime());

        $this->assertTrue($this->pool->test());

        $this->assertEquals($this->pool::CACHE_SUCCESS, $this->pool->getState());
        $this->assertNull($this->pool->getStateMessage());

    }

    public function testStats() {

        $status = $this->pool->getStats();

        $this->assertInstanceOf('\Comodojo\Cache\Components\EnhancedCacheItemPoolStats', $status);

    }

}
