<?php namespace Comodojo\SimpleCache\Tests\Manager;

use \Comodojo\SimpleCache\Manager;
use \Comodojo\SimpleCache\Providers\Memory;
use \Comodojo\Foundation\Logging\Manager as LogManager;

class AlignCacheTest extends \PHPUnit_Framework_TestCase {

    protected $logger;

    protected $item;

    public function setUp() {

        $this->logger = LogManager::create('cache', false)->getLogger();

        $this->item = (object)['name' => 'Ford', "data" => 'Perfect'];

    }

    public function testAlign() {

        $manager = new Manager(Manager::PICK_FIRST, $this->logger, true);
        $mem_a = new Memory($this->logger);
        $mem_b = new Memory($this->logger);

        $manager->addProvider($mem_a)->addProvider($mem_b);

        $this->assertTrue($manager->set($this->item->name, $this->item->data));

        $this->assertTrue($mem_a->has($this->item->name));
        $this->assertTrue($mem_b->has($this->item->name));

    }

    public function testNotAlign() {

        $manager = new Manager(Manager::PICK_FIRST, $this->logger, false);
        $mem_a = new Memory($this->logger);
        $mem_b = new Memory($this->logger);

        $manager->addProvider($mem_a)->addProvider($mem_b);

        $this->assertTrue($manager->set($this->item->name, $this->item->data));

        $this->assertTrue($mem_a->has($this->item->name));
        $this->assertFalse($mem_b->has($this->item->name));

    }

}
