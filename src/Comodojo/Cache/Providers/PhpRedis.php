<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Traits\BasicCacheItemPoolTrait;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Comodojo\Cache\Components\KeyValidator;
use \Comodojo\Cache\Traits\InstanceTrait;
use \Comodojo\Cache\Components\ItemsIterator;
use \Comodojo\Foundation\Validation\DataValidation;
use \Comodojo\Foundation\Validation\DataFilter;
use \Psr\Cache\CacheItemInterface;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Comodojo\Exception\InvalidCacheArgumentException;
use \RedisException;
use \Exception;
use \Redis;
use \DateTime;

/**
 * PhpRedis provider
 *
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

class PhpRedis extends AbstractEnhancedProvider {

    use BasicCacheItemPoolTrait;
    use InstanceTrait;

    private $connection_parameters = [];

    /**
     * Class constructorComodojo\Exception\
     *
     * @param string $server
     *      Server address (or IP)
     * @param stringc $port
     *      Server port
     * @param string $weight
     *      Server weight
     * @param string $persistent_id
     *      Persistent id
     * @param LoggerInterface $logger
     *
     * @throws CacheException
     */
    public function __construct(
        $server = '127.0.0.1',
        $port=6379,
        $timeout=0,
        LoggerInterface $logger=null
    ) {

        self::checkRedisAvailability();

        if ( empty($server) ) {
            throw new InvalidCacheArgumentException("Invalid or unspecified memcached server");
        }

        $port = DataFilter::filterPort($port, 6379);
        $timeout = DataFilter::filterInteger($timeout, 0);

        $this->connection_parameters = [$server, $port, $timeout];

        parent::__construct($logger);

        $instance = new Redis();
        if ( $instance->connect($server, $port, $timeout) === false ) {
            $this->setState(self::CACHE_ERROR, "Cannot connect to redis server $server on port $port");
        }

        $this->setInstance($instance);

    }

    public function getItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return new Item($key);

        $shadowName = "$namespace-$key";

        try {

            $data = $this->getInstance()->get($shadowName);

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return new Item($key);

        }

        if ( $data === false ) return new Item($key);

        $item = new Item($key, true);

        return $item->set(unserialize($data));

    }

    public function hasItem($key) {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        $shadowName = "$namespace-$key";

        try {

            return (bool) $this->getInstance()->exists($shadowName);

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return false;

        }

    }

    public function clear() {

        try {

            return (bool) $this->getInstance()->flushDB();

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return false;

        }

    }

    public function clearNamespace() {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        try {

            return (bool) $this->getInstance()->delete($namespace);

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return false;

        }

    }

    public function deleteItem($key) {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        $shadowName = "$namespace-$key";

        try {

            return (bool) $this->getInstance()->delete($shadowName);

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return false;

        }

    }

    public function save(CacheItemInterface $item) {

        $ttl = $item->getTtl();

        if ( $ttl < 0 ) return false;

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) $namespace = $this->setNamespaceKey();

        if ( $namespace === false ) {
            $this->setState(self::CACHE_ERROR, 'Namespace could not be saved');
            return false;
        }

        $shadowName = "$namespace-".$item->getKey();

        try {

            if ( $ttl == 0 ) {
                $return = $this->getInstance()->set($shadowName, serialize($item->getRaw()));
            } else {
                $return = $this->getInstance()->setex($shadowName, $ttl, serialize($item->getRaw()));
            }

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return false;

        }

        return (bool) $return;

    }

    public function getStats() {

        $instance = $this->getInstance();

        try {

            $objects = $instance->dbSize();
            $stats = $instance->info();

        } catch (RedisException $re ) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            $objects = 0;
            $stats = [];

        }

        return new EnhancedCacheItemPoolStats($this->getId(), 'phpredis', $this->getState(), $objects, $stats);

    }

    public function test() {

        return $this->getRedisStatus();

    }

    /**
     * Set namespace key
     *
     * @return  mixed
     */
    private function setNamespaceKey() {

        $uId = self::getUniqueId();

        try {

            $return = $this->getInstance()->set($this->getNamespace(), $uId);

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return false;

        }

        return $return === false ? false : $uId;

    }

    /**
     * Get namespace key
     *
     * @return  string
     */
    private function getNamespaceKey() {

        try {

            $return = $this->getInstance()->get($this->getNamespace());

        } catch (RedisException $re) {

            $this->setState(self::CACHE_ERROR, $re->getMessage());
            return false;

        }

        return $return;

    }

    private function getRedisStatus() {

        $instance = $this->getInstance();

        try {

            $instance->ping();
            $this->setState(self::CACHE_SUCCESS);
            return true;

        } catch (RedisException $re) {

            // connection error, try to reconnect first
            if ( $instance->connect(...$this->connection_parameters) === false ) {
                $this->setState(self::CACHE_ERROR, "Cannot connect to redis server $server on port $port");
                return false;
            } else {
                $this->setState(self::CACHE_SUCCESS);
                return true;
            }

        }

    }

    private static function checkRedisAvailability() {

        if ( class_exists('Redis') === false ) throw new CacheException("ext-redis not available");

    }

}
