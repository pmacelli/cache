<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Providers\Apcu;

class ApcuTest extends ProviderCommonCases {

    protected function setUp() {

        $this->pool = new Apcu();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
