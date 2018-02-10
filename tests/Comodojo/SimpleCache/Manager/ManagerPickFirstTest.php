<?php namespace Comodojo\SimpleCache\Tests\Manager;

use \Comodojo\SimpleCache\Tests\Utils\ManagerCommonCases;
use \Comodojo\SimpleCache\Manager;
use \Comodojo\SimpleCache\Providers\Memory;
use \Comodojo\Foundation\Logging\Manager as LogManager;

/**
 * @group manager
 * @group simplecache
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

        $this->manager = new Manager(Manager::PICK_FIRST, $logger, true, 3);

        $this->manager
            ->addProvider($this->memory_a)
            ->addProvider($this->memory_b)
            ->addProvider($this->memory_c);

    }

    /**
     * @param Item $item
     *
     * @dataProvider providerPrimitiveItems
     */
    public function testProviderMatchAlgorithm($key, $value, $ttl) {

        $this->assertTrue($this->manager->set($key, $value, $ttl));
        $this->assertTrue($this->manager->has($key));
        $new_item = $this->manager->get($key);
        $this->assertEquals($value, $new_item);
        $this->assertEquals($this->memory_a->getId(), $this->manager->getSelectedProvider()->getId());

    }

    public function testSimulatedFailure() {

        $items = $this->providerPrimitiveItems();

        foreach ($items as $item) {
            $this->assertTrue($this->manager->set($item[0], $item[1], $item[2]));
        }

        $this->assertTrue($this->manager->has($items[0][0]));

        $this->assertEquals($this->memory_a->getId(), $this->manager->getSelectedProvider()->getId());

        $this->memory_a->setState(Memory::CACHE_ERROR, 'test');

        $this->assertTrue($this->manager->has($items[0][0]));

        $this->assertEquals($this->memory_b->getId(), $this->manager->getSelectedProvider()->getId());

        sleep(5);

        $this->assertTrue($this->manager->has($items[0][0]));

        $this->assertEquals($this->memory_a->getId(), $this->manager->getSelectedProvider()->getId());

    }

}
