<?php namespace Comodojo\Cache\Tests\Manager;

use \Comodojo\Cache\Tests\Utils\ManagerCommonCases;
use \Comodojo\Cache\Manager;
use \Comodojo\Cache\Providers\Memory;
use \Comodojo\Cache\Item;
use \Comodojo\Foundation\Logging\Manager as LogManager;

class ManagerPickAllTest extends ManagerCommonCases {

    protected $manager;
    protected $memory_a;
    protected $memory_b;
    protected $memory_c;

    public function setUp() {

        $logger = LogManager::create('cache', false)->getLogger();

        $this->memory_a = new Memory([], $logger);
        $this->memory_b = new Memory([], $logger);
        $this->memory_c = new Memory([], $logger);

        $this->manager = new Manager(Manager::PICK_ALL, $logger, true, 5);

        $this->manager
            ->addProvider($this->memory_a, 100)
            ->addProvider($this->memory_b, 50)
            ->addProvider($this->memory_c, 0);

    }

    public function testProviderMatchAlgorithm() {

        $results = [];

        foreach ($this->providerPrimitiveItems() as $item_a) {

            $item = $item_a[0];

            $this->assertTrue($this->manager->save($item));

        }

        foreach ($this->providerPrimitiveItems() as $item_a) {

            $item = $item_a[0];

            $this->assertTrue($this->manager->hasItem($item->getKey()));

            $this->assertEquals($item->getRaw(), $this->manager->getItem($item->getKey())->get());

        }

        $new_item = new Item('fakeitem');
        $new_item->set('this is a fake item');

        $this->assertFalse($this->manager->getItem('fakeitem')->isHit());

        $this->memory_a->save($new_item);

        $this->assertFalse($this->manager->hasItem('fakeitem'));
        $this->assertFalse($this->manager->getItem('fakeitem')->isHit());

    }

}
