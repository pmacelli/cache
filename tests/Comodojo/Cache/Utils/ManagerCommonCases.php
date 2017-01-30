<?php namespace Comodojo\Cache\Tests\Utils;

use \Comodojo\Cache\Manager;
use \Comodojo\Cache\Providers\Apcu;
use \Comodojo\Cache\Providers\Memcached;
use \Comodojo\Cache\Providers\Memory;
use \Comodojo\Cache\Item;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \DateTime;

abstract class ManagerCommonCases extends \PHPUnit_Framework_TestCase {

    /**
     * Provides a set of Items to test the manager
     *
     * @return Item
     */
    public function providerPrimitiveItems() {
        return [
            [self::itemMaker('Marvin', 'sad robot', 20)],
            [self::itemMaker('Ford', 'perfect', 20)],
            [self::itemMaker('Gag', 'Halfrunt', 20)],
            [self::itemMaker('Hotblack', 'Desiato', 20)],
            [self::itemMaker('Oolon', 'Colluphid', 20)]
        ];
    }

    protected static function itemMaker($key, $data, $ttl) {

        $item = new Item($key);

        $item->set($data);
        $item->expiresAfter($ttl);

        return $item;

    }

}
