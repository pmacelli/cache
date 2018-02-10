<?php namespace Comodojo\SimpleCache\Tests\Manager;

use \Comodojo\SimpleCache\Manager;
use \Comodojo\Foundation\Logging\Manager as LogManager;

/**
 * @group manager
 * @group simplecache
 */
class EmptyManagerTest extends \PHPUnit_Framework_TestCase {

    protected $manager;

    public function setUp() {

        $logger = LogManager::create('cache', false)->getLogger();

        $this->manager = new Manager(Manager::PICK_FIRST, $logger, true, 5);

    }

    public function testPsr() {

        $this->assertTrue($this->manager->set('Ford','Perfect'));
        $this->assertTrue($this->manager->setMultiple(['Ford'=>'Perfect','Marvin'=>'Sad Robot']));

        $items = $this->manager->getMultiple(['Ford','Marvin']);
        foreach ($items as $key => $value) {
            $this->assertNull($value);
        }

        $this->assertFalse($this->manager->has('Ford'));

        $this->assertNull($this->manager->get('Ford'));

        $this->assertFalse($this->manager->delete('Ford'));
        $this->assertFalse($this->manager->deleteMultiple(['Ford','Marvin']));

        $this->assertTrue($this->manager->clear());
        $this->assertTrue($this->manager->clearNamespace());

        $this->manager->setNamespace('TEST');
        $this->assertEquals('TEST',$this->manager->getNamespace());

    }

}
