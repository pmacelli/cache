<?php namespace Comodojo\SimpleCache\Tests\Manager;

use \Comodojo\SimpleCache\Tests\Utils\ManagerCommonCases;
use \Comodojo\SimpleCache\Manager;
use \Comodojo\SimpleCache\Providers\Memory;
use \Comodojo\Foundation\Logging\Manager as LogManager;

/**
 * @group manager
 * @group simplecache
 */
class ManagerPickRandomTest extends ManagerCommonCases {

    protected $manager;
    protected $memory_a;
    protected $memory_b;
    protected $memory_c;

    public function setUp() {

        $logger = LogManager::create('cache', false)->getLogger();

        $this->memory_a = new Memory([], $logger);
        $this->memory_b = new Memory([], $logger);
        $this->memory_c = new Memory([], $logger);

        $this->manager = new Manager(Manager::PICK_RANDOM, $logger, true, 5);

        $this->manager
            ->addProvider($this->memory_a)
            ->addProvider($this->memory_b)
            ->addProvider($this->memory_c);

    }

    public function testProviderMatchAlgorithm() {

        $items = $this->providerPrimitiveItems();
        $results = [];

        foreach ($items as $item) {
            $this->assertTrue($this->manager->set($item[0], $item[1], $item[2]));
        }

        foreach ($items as $item) {

            $this->assertTrue($this->manager->has($item[0]));

            $results[] = $this->manager->getSelectedProvider()->getId();

        }

        $this->assertGreaterThan(1, count(array_unique($results)));

    }

}
