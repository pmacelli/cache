<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\EnhancedProviderCommonCases;
use \Comodojo\Cache\Providers\PhpRedis;

/**
 * @group provider
 * @group cache
 * @group phpredis
 */
class PhpRedisTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->pool = new PhpRedis();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
