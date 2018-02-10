<?php namespace Comodojo\SimpleCache\Components;

use \Comodojo\Cache\Components\ConfigurationParser as CacheConfigurationParser;
use \Psr\Log\LoggerInterface;
use \Comodojo\SimpleCache\Providers\Apc as SimpleCacheApc;
use \Comodojo\SimpleCache\Providers\Apcu as SimpleCacheApcu;
use \Comodojo\SimpleCache\Providers\Filesystem as SimpleCacheFilesystem;
use \Comodojo\SimpleCache\Providers\Memcached as SimpleCacheMemcached;
use \Comodojo\SimpleCache\Providers\Memory as SimpleCacheMemory;
use \Comodojo\SimpleCache\Providers\PhpRedis as SimpleCachePhpRedis;
use \Comodojo\SimpleCache\Providers\Vacuum as SimpleCacheVacuum;

/**
 * @package     Comodojo Cache
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

class ConfigurationParser extends CacheConfigurationParser {

    protected static function BuildApcProvider(LoggerInterface $logger) {

        return new SimpleCacheApc([], $logger);

    }

    protected static function BuildApcuProvider(LoggerInterface $logger) {

        return new SimpleCacheApcu([], $logger);

    }

    protected static function BuildFilesystemProvider(array $properties, LoggerInterface $logger) {

        return new SimpleCacheFilesystem($properties, $logger);

    }

    protected static function BuildMemcachedProvider(array $properties, LoggerInterface $logger) {

        return new SimpleCacheMemcached($properties, $logger);

    }

    protected static function BuildMemoryProvider(LoggerInterface $logger) {

        return new SimpleCacheMemory([], $logger);

    }

    protected static function BuildPhpRedisProvider(array $properties, LoggerInterface $logger) {

        return new SimpleCachePhpRedis($properties, $logger);

    }

    protected static function BuildVacuumProvider(LoggerInterface $logger) {

        return new SimpleCacheVacuum([], $logger);

    }

}
