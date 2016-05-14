<?php

use \Comodojo\Cache\Tests\CommonCases;

class DatabaseCacheTest extends CommonCases {

    protected function setUp() {

        $edb = \Comodojo\Cache\DatabaseCache::getDatabase('MYSQLI', '127.0.0.1', 3306, 'comodojocache', 'root', 'comodojo');

        $this->cache = new \Comodojo\Cache\DatabaseCache($edb, 'cache', 'cmdj_');

    }

    protected function tearDown() {

        unset($this->cache);

    }

}
