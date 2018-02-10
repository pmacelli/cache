<?php namespace Comodojo\SimpleCache\Tests\Manager;

use \Comodojo\SimpleCache\Tests\Utils\ManagerCommonCases;
use \Comodojo\SimpleCache\Manager;
use \Comodojo\SimpleCache\Providers\Memory;
use \Comodojo\Foundation\Logging\Manager as LogManager;

/**
 * @group manager
 * @group simplecache
 */
class ManagerPickTraverseTest extends ManagerCommonCases {

    protected $manager;
    protected $memory_a;
    protected $memory_b;
    protected $memory_c;

    public function setUp() {

        $logger = LogManager::create('cache', false)->getLogger();

        $this->memory_a = new Memory([], $logger);
        $this->memory_b = new Memory([], $logger);
        $this->memory_c = new Memory([], $logger);

        $this->manager = new Manager(Manager::PICK_TRAVERSE, $logger, true, 5);

        $this->manager
            ->addProvider($this->memory_a)
            ->addProvider($this->memory_b)
            ->addProvider($this->memory_c);

    }

    public function testProviderMatchAlgorithm() {

        $items = $this->providerPrimitiveItems();
        $results = [];
        $providers = [$this->memory_a, $this->memory_b, $this->memory_c];

        foreach ($items as $item) {
            $provider = $providers[array_rand($providers)];
            $this->assertTrue($provider->set($item[0], $item[1], $item[2]));
        }

        foreach ($items as $item) {

            $this->assertTrue($this->manager->has($item[0]));

        }

    }

}
