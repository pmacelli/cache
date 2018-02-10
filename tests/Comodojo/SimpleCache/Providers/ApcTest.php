<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Apc;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

/**
 * @group provider
 * @group simplecache
 * @group apc
 */
class ApcTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->provider = new Apc();

    }

}
