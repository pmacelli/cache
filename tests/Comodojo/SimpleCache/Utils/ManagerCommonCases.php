<?php namespace Comodojo\SimpleCache\Tests\Utils;

abstract class ManagerCommonCases extends \PHPUnit_Framework_TestCase {

    /**
     * Provides a set of Items to test the manager
     *
     * @return Item
     */
    public function providerPrimitiveItems() {
        return [
            ['Marvin', 'sad robot', 20],
            ['Ford', 'perfect', 20],
            ['Gag', 'Halfrunt', 20],
            ['Hotblack', 'Desiato', 20],
            ['Oolon', 'Colluphid', 20]
        ];
    }

}
