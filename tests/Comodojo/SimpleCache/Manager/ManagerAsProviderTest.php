<?php namespace Comodojo\SimpleCache\Tests\Manager;

use \Comodojo\SimpleCache\Tests\Utils\SimpleCacheCommonCases;
use \Comodojo\SimpleCache\Manager;
use \Comodojo\SimpleCache\Providers\Memory;
// use \Comodojo\SimpleCache\Providers\Memcached;

/**
 * @group manager
 * @group simplecache
 */
class ManagerAsProviderTest extends SimpleCacheCommonCases {

    protected function setUp() {

        $apc = new Memory();
        $memcached = new Memory();

        $this->provider = new Manager();
        $this->provider
            ->addProvider($apc, 20)
            ->addProvider($memcached, 10);

    }

    public function testManagerStats() {

        $stats = $this->provider->getStats();

        $this->assertInternalType('array', $stats);

        $this->assertEquals(2, count($stats));

        foreach ($stats as $stat) {
            $this->assertInstanceOf('\Comodojo\Cache\Components\EnhancedCacheItemPoolStats', $stat);
        }

    }

}
