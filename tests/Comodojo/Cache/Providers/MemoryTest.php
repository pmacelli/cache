<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\EnhancedProviderCommonCases;
use \Comodojo\Cache\Providers\Memory;

class MemoryTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->pool = new Memory();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
