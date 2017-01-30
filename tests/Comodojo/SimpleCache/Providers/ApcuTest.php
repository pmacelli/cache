<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Apcu;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

class ApcuTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->provider = new Apcu();

    }

}
