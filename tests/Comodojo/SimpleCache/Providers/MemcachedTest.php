<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Memcached;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

class MemcachedTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->provider = new Memcached('127.0.0.1');

    }

}
