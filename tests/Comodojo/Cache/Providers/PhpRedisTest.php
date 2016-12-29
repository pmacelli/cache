<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Providers\PhpRedis;

class PhpRedisTest extends ProviderCommonCases {

    protected function setUp() {

        $this->pool = new PhpRedis();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
