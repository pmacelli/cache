<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\EnhancedProviderCommonCases;
use \Comodojo\Cache\Providers\Apc;

class ApcTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->pool = new Apc();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
