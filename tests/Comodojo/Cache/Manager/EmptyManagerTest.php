<?php namespace Comodojo\Cache\Tests\Manager;

use \Comodojo\Cache\Manager;
use \Comodojo\Foundation\Logging\Manager as LogManager;

class EmptyManagerTest extends \PHPUnit_Framework_TestCase {

    protected $manager;

    public function setUp() {

        $logger = LogManager::create('cache', false)->getLogger();

        $this->manager = new Manager(Manager::PICK_FIRST, $logger, true, 5);

    }

    public function testPsr() {

        $item = $this->manager->getItem('Ford');
        $this->assertFalse($item->isHit());

        $item->set('Perfect');

        $this->assertTrue($this->manager->save($item));
        $this->assertFalse($this->manager->hasItem('Ford'));

        $items = $this->manager->getItems(['Ford','Marvin']);
        foreach ($items as $item) {
            $this->assertFalse($item->isHit());
        }

        $this->assertFalse($this->manager->deleteItem('Ford'));
        $this->assertFalse($this->manager->deleteItems(['Ford','Marvin']));

        $this->assertTrue($this->manager->saveDeferred($item));
        $this->assertTrue($this->manager->commit());

        $this->assertTrue($this->manager->clear());
        $this->assertTrue($this->manager->clearNamespace());
        $this->manager->setNamespace('TEST');
        $this->assertEquals('TEST',$this->manager->getNamespace());

    }

}
