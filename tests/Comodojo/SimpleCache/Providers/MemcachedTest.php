<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Memcached;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

/**
 * @group provider
 * @group simplecache
 * @group memcached
 */
class MemcachedTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->provider = new Memcached(['server' => '127.0.0.1']);

    }

}
