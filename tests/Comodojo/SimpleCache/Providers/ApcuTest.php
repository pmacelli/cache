<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Apcu;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

/**
 * @group provider
 * @group simplecache
 * @group apcu
 */
class ApcuTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $this->provider = new Apcu();

    }

}
