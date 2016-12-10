<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Providers\Memory;

class MemoryTest extends ProviderCommonCases {

    protected function setUp() {

        $this->pool = new Memory();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
