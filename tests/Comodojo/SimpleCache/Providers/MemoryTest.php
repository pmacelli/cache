<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Memory;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

class MemoryTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->provider = new Memory();

    }

}
