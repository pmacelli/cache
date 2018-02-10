<?php namespace Comodojo\Cache\Tests\Providers;

use \Comodojo\Cache\Tests\Utils\EnhancedProviderCommonCases;
use \Comodojo\Cache\Providers\Filesystem;

/**
 * @group provider
 * @group cache
 * @group filesystem
 */
class FilesystemTest extends EnhancedProviderCommonCases {

    protected function setUp() {

        $cache_folder = realpath(__DIR__ . "/../../../")."/localcache";

        $this->pool = new Filesystem(['cache_folder' => $cache_folder]);

    }

    protected function tearDown() {

        unset($this->pool);

    }

}
