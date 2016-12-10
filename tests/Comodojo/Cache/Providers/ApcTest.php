<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Providers\Apc;

class ApcTest extends ProviderCommonCases {

    protected function setUp() {

        $this->pool = new Apc();

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
