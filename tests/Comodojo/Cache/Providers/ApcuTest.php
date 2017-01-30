<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\EnhancedProviderCommonCases;
use \Comodojo\Cache\Providers\Apcu;

class ApcuTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->pool = new Apcu();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
