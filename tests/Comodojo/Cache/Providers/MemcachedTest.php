<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Providers\Memcached;

class MemcachedTest extends ProviderCommonCases {

    protected function setUp() {

        $this->pool = new Memcached('127.0.0.1');

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
