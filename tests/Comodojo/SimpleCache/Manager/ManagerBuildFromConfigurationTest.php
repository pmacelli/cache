<?php namespace Comodojo\SimpleCache\Tests\Manager;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\SimpleCache\Manager;
use \Comodojo\Foundation\Logging\Manager as LogManager;

/**
 * @group manager
 * @group simplecache
 */
class ManagerBuildFromConfigurationTest extends \PHPUnit_Framework_TestCase {

    protected static $local_config = [
        "cache" => [
            "enable" => true,
            "pick_mode" => "PICK_RANDOM",
            "providers" => [
                "test_a" => [
                    "type" => "apc"
                ],
                "test_b" => [
                    "type" => "apcu",
                    "weight" => 11
                ],
                "test_c" => [
                    "type" => "memory"
                ],
                "test_d" => [
                    "type" => "filesystem",
                    "cache_folder" => "localcache",
                    "weight" => 10
                ],
                "test_e" => [
                    "type" => "memcached"
                ],
                "test_f" => [
                    "type" => "phpredis",
                    "server" => "127.0.0.1"
                ]
            ]
        ]
    ];

    protected $manager;

    protected function setUp() {

        $config = array_merge(self::$local_config, array(
            "base-path" => realpath(dirname(__FILE__)."/../../../")
        ));

        $configuration = new Configuration( $config );

        $logger = LogManager::create('cache',false)->getLogger();

        $this->manager = Manager::createFromConfiguration($configuration, $logger);

    }

    public function testGetSet() {

        $this->manager->set('Ford-simpleconf','Perfect');

        $item = $this->manager->get('Ford-simpleconf');
        $this->assertEquals('Perfect', $item);

    }

}
