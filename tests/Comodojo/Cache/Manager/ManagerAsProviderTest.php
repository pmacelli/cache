<?php namespace Comodojo\Cache\Tests\Manager;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Manager;
use \Comodojo\Cache\Providers\Apc;
use \Comodojo\Cache\Providers\Memcached;

class ManagerAsProviderTest extends ProviderCommonCases {

    protected function setUp() {

        $apc = new Apc();
        $memcached = new Memcached('127.0.0.1');

        $this->pool = new Manager();
        $this->pool
            ->addProvider($apc, 20)
            ->addProvider($memcached, 10);

    }

    protected function tearDown() {

        unset($this->pool);

    }

    public function testManagerStats() {

        $stats = $this->pool->getStats();

        $this->assertInternalType('array', $stats);

        $this->assertEquals(2, count($stats));

        foreach ($stats as $stat) {
            $this->assertInstanceOf('\Comodojo\Cache\Components\EnhancedCacheItemPoolStats', $stat);
        }

    }

}
