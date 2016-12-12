<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\ProviderCommonCases;
use \Comodojo\Cache\Providers\Filesystem;

class FilesystemTest extends ProviderCommonCases {

    protected function setUp() {

        $cache_folder = __DIR__ . "/../../../localcache/";

        $this->pool = new Filesystem($cache_folder);

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
