<?php namespace Comodojo\SimpleCache\Tests\Utils;

use \DateTime;

class EnhancedProviderCommonCases extends SimpleCacheCommonCases {

    public function testGetId() {

        $result = $this->provider->getId();

        $this->assertNotEmpty($result);

    }

    public function testChangeState() {

        $message = 'Marvin seems sad tonight';

        $this->assertEquals(0, $this->provider->getState());

        $this->assertNull($this->provider->getStateMessage());

        $time = new DateTime('now');

        $this->provider->setState(1, $message);

        $this->assertEquals(1, $this->provider->getState());

        $this->assertEquals($message, $this->provider->getStateMessage());

        $this->assertGreaterThanOrEqual($time, $this->provider->getStateTime());

    }

    public function testSimulatedFailure() {

        $status = $this->provider::CACHE_ERROR;

        $message = 'this is a simulated failure';

        $this->provider->setState($status, $message);

        $this->assertEquals($status, $this->provider->getState());
        $this->assertEquals($message, $this->provider->getStateMessage());
        $this->assertInstanceOf('\DateTimeInterface', $this->provider->getStateTime());

        $this->assertTrue($this->provider->test());

        $this->assertEquals($this->provider::CACHE_SUCCESS, $this->provider->getState());
        $this->assertNull($this->provider->getStateMessage());

    }

    public function testStats() {

        $status = $this->provider->getStats();

        $this->assertInstanceOf('\Comodojo\Cache\Components\EnhancedCacheItemPoolStats', $status);

    }

}
