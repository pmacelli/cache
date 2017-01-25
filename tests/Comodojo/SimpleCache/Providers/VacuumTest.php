<?php namespace Comodojo\SimpleCache\Tests\Providers;

use \Comodojo\SimpleCache\Providers\Vacuum;
use \Comodojo\SimpleCache\Tests\Utils\EnhancedProviderCommonCases;

class VacuumTest extends \PHPUnit_Framework_TestCase {

    protected function setUp() {

        $this->provider = new Vacuum();

    }

    protected function tearDown() {

        unset($this->provider);

    }

    public function testSetGetHasDeleteItem() {

        $this->assertTrue($this->provider->set('Ford', 'i\'m perfect'));
        $this->assertFalse($this->provider->has('Ford'));
        $this->assertNull($this->provider->get('Ford'));
        $this->assertFalse($this->provider->delete('Ford'));

    }


    public function testClear() {

        $this->assertTrue($this->provider->clear());
        $this->assertTrue($this->provider->clearNamespace());

    }

}
