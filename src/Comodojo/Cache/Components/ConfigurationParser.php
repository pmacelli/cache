<?php namespace Comodojo\Cache\Components;

use \Comodojo\Foundation\Base\Configuration;
use \Comodojo\Foundation\Validation\DataFilter;
use \Psr\Log\LoggerInterface;
use \Comodojo\Cache\Providers\Apc as CacheApc;
use \Comodojo\Cache\Providers\Apcu as CacheApcu;
use \Comodojo\Cache\Providers\Filesystem as CacheFilesystem;
use \Comodojo\Cache\Providers\Memcached as CacheMemcached;
use \Comodojo\Cache\Providers\Memory as CacheMemory;
use \Comodojo\Cache\Providers\PhpRedis as CachePhpRedis;
use \Comodojo\Cache\Providers\Vacuum as CacheVacuum;

/**
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <marco.giovinazzi@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class ConfigurationParser {

    const DEFAULT_CACHE_FOLDER = 'cache';

    protected static $algorithms = array(
        'PICK_FIRST' => 1,
        'PICK_LAST' => 2,
        'PICK_RANDOM' => 3,
        'PICK_BYWEIGHT' => 4,
        'PICK_ALL' => 4,
        'PICK_TRAVERSE' => 6
    );

    public static function parse(Configuration $configuration, LoggerInterface $logger) {

        return [
            self::parseManagerConfiguration($configuration, $logger),
            self::buildProviders($configuration, $logger)
        ];

    }

    protected static function BuildApcProvider(LoggerInterface $logger) {

        return new CacheApc($logger);

    }

    protected static function BuildApcuProvider(LoggerInterface $logger) {

        return new CacheApcu($logger);

    }

    protected static function BuildFilesystemProvider($cache_folder, LoggerInterface $logger) {

        return new CacheFilesystem($cache_folder, $logger);

    }

    protected static function BuildMemcachedProvider($server, $port, $weight, $persistentid, LoggerInterface $logger) {

        return new CacheMemcached($server, $port, $weight, $persistentid, $logger);

    }

    protected static function BuildMemoryProvider(LoggerInterface $logger) {

        return new CacheMemory($logger);

    }

    protected static function BuildPhpRedisProvider($server, $port, $timeout, LoggerInterface $logger) {

        return new CachePhpRedis($server, $port, $timeout, $logger);

    }

    protected static function BuildVacuumProvider(LoggerInterface $logger) {

        return new CacheVacuum($logger);

    }

    protected static function parseManagerConfiguration(Configuration $configuration, LoggerInterface $logger) {

        $cache = $configuration->get('cache');

        $stdConfig = [
            'pick_mode' => null,
            'logger' => $logger,
            'align_cache' => true,
            'flap_interval' => null
        ];

        if ( $cache !== null && is_array($cache) ) {
            $lower_cache = array_change_key_case($cache, CASE_LOWER);
            if ( isset($lower_cache['logger']) ) unset($lower_cache['logger']);
            $stdConfig = array_merge($stdConfig, array_intersect_key($lower_cache, $stdConfig));
        }

        if ($stdConfig['pick_mode'] !== null) $stdConfig['pick_mode'] = self::getPickMode($stdConfig['pick_mode']);

        return array_values($stdConfig);

    }

    protected static function buildProviders(Configuration $configuration, LoggerInterface $logger = null) {

        $cache = $configuration->get('cache');
        $build = [];

        if ( $cache === null ) return $build;

        $lower_cache = array_change_key_case($cache, CASE_LOWER);

        if ( !isset($lower_cache['providers']) || !is_array($lower_cache['providers']) ) return $build;

        $providers = $lower_cache['providers'];

        foreach ($providers as $name => $specs) {

            if ( !is_array($specs) ) {
                $logger->error("Invalid specs for cache provider: $name");
                continue;
            }

            $spec = array_change_key_case($specs, CASE_LOWER);

            if ( empty($spec['type']) ) {
                $logger->error("Missing type for cache provider: $name");
                continue;
            }

            $type = strtoupper($spec['type']);

            switch ($type) {

                case 'APC':
                    $provider = static::BuildApcProvider($logger);
                    break;

                case 'APCU':
                    $provider = static::BuildApcuProvider($logger);
                    break;

                case 'FILESYSTEM':

                    $stdConfig = [
                        'cache_folder' => static::DEFAULT_CACHE_FOLDER,
                        'logger' => $logger
                    ];

                    if ( isset($spec['cache_folder']) ) {
                        if ( $spec['cache_folder'][0] == "/" ) {
                            $stdConfig['cache_folder'] = $spec['cache_folder'];
                        } else {
                            $stdConfig['cache_folder'] = $configuration->get('base-path')."/".$spec['cache_folder'];
                        }
                    }

                    $provider = static::BuildFilesystemProvider(...array_values($stdConfig));

                    break;

                case 'MEMCACHED':

                    $stdConfig = [
                        'server' => '127.0.0.1',
                        'port' => 11211,
                        'weight' => 0,
                        'persistent_id' => null,
                        'logger' => $logger
                    ];

                    if ( isset($spec['logger']) ) unset($spec['logger']);
                    $stdConfig = array_merge($stdConfig, array_intersect_key($spec, $stdConfig));

                    $provider = static::BuildMemcachedProvider(...array_values($stdConfig));
                    break;

                case 'MEMORY':
                    $provider = static::BuildMemoryProvider($logger);
                    break;

                case 'PHPREDIS':

                    $stdConfig = [
                        'server' => '127.0.0.1',
                        'port' => 6379,
                        'timeout' => 0,
                        'logger' => $logger
                    ];

                    if ( isset($spec['logger']) ) unset($spec['logger']);
                    $stdConfig = array_merge($stdConfig, array_intersect_key($spec, $stdConfig));

                    $provider = static::BuildPhpRedisProvider(...array_values($stdConfig));
                    break;

                case 'VACUUM':
                    $provider = static::BuildVacuumProvider($logger);
                    break;

                default:
                    $logger->error("Unknown type $type for cache provider: $name");
                    continue;
                    break;

            }

            $build[$name] = (object) [
                "instance" => $provider,
                "weight" => isset($spec['weight']) ?
                    DataFilter::filterInteger($spec['weight'], 0, 100, 0) : 0
            ];

        }

        return $build;

    }

    protected static function getPickMode($algorithm = null) {

        $algorithm = strtoupper($algorithm);

        if ( array_key_exists($algorithm, self::$algorithms) ) return self::$algorithms[$algorithm];

        return self::$algorithms['PICK_FIRST'];

    }

}
