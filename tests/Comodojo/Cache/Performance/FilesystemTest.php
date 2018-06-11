<?php namespace Comodojo\Cache\Performance;

use \Comodojo\Cache\Drivers\AbstractDriver;
use \Comodojo\Cache\Drivers\FilesystemXattr as FilesystemXattrDriver;
use \Comodojo\Cache\Drivers\FilesystemGhost as FilesystemGhostDriver;

/**
 * @group performance
 * @group perf-filesystem
 */
class FilesystemTest extends \PHPUnit_Framework_TestCase {

    const CACHE_SIZE = 10000;

    protected static $folder;

    protected static $data = [];

    public static function setUpBeforeClass() {

        $base_string = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';

        for ($i=0; $i < self::CACHE_SIZE; $i++) {
            self::$data[uniqid()] = substr(str_shuffle($base_string), 0, 10);
        }

        self::$folder = realpath(__DIR__ . "/../../../")."/localcache/";

    }

    public function testGhostDriver() {

        $driver = new FilesystemGhostDriver(['cache-folder'=>self::$folder]);

        list($t0, $t1, $t2, $t3) = $this->runT($driver);

        $this->printRes('GHOST', $t0, $t1, $t2, $t3);

        $this->emptyTestDirectory();

    }

    public function testXattrDriver() {

        $driver = new FilesystemXattrDriver(['cache-folder'=>self::$folder]);

        list($t0, $t1, $t2, $t3) = $this->runT($driver);

        $this->printRes('XATTR', $t0, $t1, $t2, $t3);

        $this->emptyTestDirectory();

    }

    protected function runT(AbstractDriver $driver) {

        $t0 = microtime(true);

        foreach (self::$data as $key => $value) {
            $driver->set($key, 'test', $value, 600);
        }

        $t1 = microtime(true);

        foreach (self::$data as $key => $value) {
            $driver->has($key, 'test');
        }

        $t2 = microtime(true);

        foreach (self::$data as $key => $value) {
            $driver->get($key, 'test');
        }

        $t3 = microtime(true);

        return [$t0, $t1, $t2, $t3];

    }

    protected function printRes($mode, $t0, $t1, $t2, $t3) {

        print_r(
            "\n> ($mode) set 10k data: ".($t1-$t0)." secs".
            "\n> ($mode) check 10k keys: ".($t2-$t1)." secs".
            "\n> ($mode) get 10k data: ".($t3-$t2)." secs".
            "\n> ($mode) total test time: ".($t3-$t0)." secs"
        );

    }

    protected function emptyTestDirectory() {

        array_map('unlink', glob(self::$folder."/*"));

    }

}
