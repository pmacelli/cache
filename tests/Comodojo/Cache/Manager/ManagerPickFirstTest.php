<?php namespace Comodojo\Cache\Tests\Manager;

use \Comodojo\Cache\Tests\Utils\ManagerCommonCases;
use \Comodojo\Cache\Manager;
use \Comodojo\Cache\Providers\Memory;
use \Comodojo\Cache\Item;
use \Comodojo\Foundation\Logging\Manager as LogManager;

/**
 * @group manager
 * @group cache
 */
class ManagerPickFirstTest extends ManagerCommonCases {

    protected $manager;
    protected $memory_a;
    protected $memory_b;
    protected $memory_c;

    public function setUp() {

        $logger = LogManager::create('cache', false)->getLogger();

        $this->memory_a = new Memory([], $logger);
        $this->memory_b = new Memory([], $logger);
        $this->memory_c = new Memory([], $logger);

        $this->manager = new Manager(Manager::PICK_FIRST, $logger, true, 5);

        $this->manager
            ->addProvider($this->memory_a, 100)
            ->addProvider($this->memory_b, 50)
            ->addProvider($this->memory_c, 0);

    }

    /**
     * @param Item $item
     *
     * @dataProvider providerPrimitiveItems
     */
    public function testProviderMatchAlgorithm(Item $item) {

        $key = $item->getKey();
        $data = $item->getRaw();
        $this->assertTrue($this->manager->save($item));
        $this->assertTrue($this->manager->hasItem($key));
        $new_item = $this->manager->getItem($key);
        $this->assertEquals($data, $new_item->get());
        $this->assertEquals($this->memory_a->getId(), $this->manager->getSelectedProvider()->getId());

    }

}
