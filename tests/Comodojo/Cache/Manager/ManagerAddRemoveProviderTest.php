<?php namespace Comodojo\Cache\Tests\Manager;

use \Comodojo\Cache\Manager;
use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Comodojo\Cache\Providers\Memory;

/**
 * @group manager
 * @group cache
 */
class ManagerAddRemoveProviderTest extends \PHPUnit_Framework_TestCase {

    protected $manager;

    protected $logger;

    public function setUp() {

        $this->logger = LogManager::create('cache', false)->getLogger();

        $this->manager = new Manager(Manager::PICK_FIRST, $this->logger, true, 5);

    }

    public function testAddRemoveProvider() {

        $memory_a = new Memory([], $this->logger);
        $memory_b = new Memory([], $this->logger);

        $this->assertCount(0, $this->manager->getProviders());

        $this->manager->addProvider($memory_a);
        $this->manager->addProvider($memory_b);

        $this->assertCount(2, $this->manager->getProviders());

        $this->manager->removeProvider($memory_a->getId());

        $this->assertCount(1, $this->manager->getProviders());

        $this->assertEquals($memory_b->getId(), $this->manager->getProvider($memory_b->getId())->getId());

    }

}
