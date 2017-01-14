<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\EnhancedProviderCommonCases;
use \Comodojo\Cache\Providers\Memcached;

class MemcachedTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->pool = new Memcached('127.0.0.1');

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
