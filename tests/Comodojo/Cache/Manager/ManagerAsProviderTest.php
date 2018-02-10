<?php namespace Comodojo\Cache\Tests\Manager;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Manager;
use \Comodojo\Cache\Providers\Memory;
// use \Comodojo\Cache\Providers\Apc;
// use \Comodojo\Cache\Providers\Memcached;

/**
 * @group manager
 * @group cache
 */
class ManagerAsProviderTest extends ProviderCommonCases {

    protected function setUp() {

        $apc = new Memory();
        $memcached = new Memory();

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
