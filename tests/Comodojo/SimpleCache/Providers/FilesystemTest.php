<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Filesystem;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

class FilesystemTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $cache_folder = realpath(__DIR__ . "/../../../")."/localcache";

        $this->provider = new Filesystem(['cache_folder' => $cache_folder]);

    }

}
