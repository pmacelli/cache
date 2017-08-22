<?php namespace Comodojo\Cache\Tests\Manager;

use \Comodojo\Cache\Manager;
use \Comodojo\Cache\Providers\Memory;
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
        $mem_a = new Memory([], $this->logger);
        $mem_b = new Memory([], $this->logger);

        $manager->addProvider($mem_a)->addProvider($mem_b);

        $item = $manager->getItem($this->item->name);
        $this->assertFalse($item->isHit());
        $item->set($this->item->data);
        $this->assertTrue($manager->save($item));

        $this->assertTrue($mem_a->hasItem($this->item->name));
        $this->assertTrue($mem_b->hasItem($this->item->name));

    }

    public function testNotAlign() {

        $manager = new Manager(Manager::PICK_FIRST, $this->logger, false);
        $mem_a = new Memory([], $this->logger);
        $mem_b = new Memory([], $this->logger);

        $manager->addProvider($mem_a)->addProvider($mem_b);

        $item = $manager->getItem($this->item->name);
        $this->assertFalse($item->isHit());
        $item->set($this->item->data);
        $this->assertTrue($manager->save($item));

        $this->assertTrue($mem_a->hasItem($this->item->name));
        $this->assertFalse($mem_b->hasItem($this->item->name));

    }

}
