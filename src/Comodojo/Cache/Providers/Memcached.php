<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Components\BasicCacheItemPoolTrait;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Comodojo\Cache\Components\KeyValidator;
use \Comodojo\Cache\Components\InstanceTrait;
use \Comodojo\Cache\Components\ItemsIterator;
use \Comodojo\Foundation\Validation\DataValidation;
use \Comodojo\Foundation\Validation\DataFilter;
use \Psr\Cache\CacheItemInterface;
use \Comodojo\Exception\CacheException;
use \Comodojo\Exception\InvalidCacheArgumentException;
use \Memcached as MemcachedInstance;
use \Exception;
use \DateTime;

/**
 * Memcached provider
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

class Memcached extends AbstractEnhancedProvider {

    use BasicCacheItemPoolTrait;
    use InstanceTrait;

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
        $port=11211,
        $weight=0,
        $persistent_id=null,
        LoggerInterface $logger=null
    ) {

        self::checkMemcachedAvailability();

        if ( empty($server) ) {
            throw new InvalidCacheArgumentException("Invalid or unspecified memcached server");
        }

        if ( $persistent_id !== null && DataValidation::validateString($persistent_id) === false ) {
            throw new InvalidCacheArgumentException("Invalid persistent id");
        }

        $port = DataFilter::filterPort($port, 11211);
        $weight = DataFilter::filterInteger($weight);

        parent::__construct($logger);

        $this->setInstance(new MemcachedInstance($persistent_id));
        $this->getInstance()->addServer($server, $port, $weight);

        $this->getMemcachedStatus();

    }

    public function getItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return new Item($key);

        $shadowName = "$namespace-$key";

        $data = $this->getInstance()->get($shadowName);

        if ( $data === false ) return new Item($key);

        $item = new Item($key, true);

        return $item->set(unserialize($data));

    }

    public function getItems(array $keys = []) {

        $items = new ItemsIterator();

        if ( empty($keys) ) return $items;

        array_walk($keys, function ($value) {
            if ( KeyValidator::validateKey($value) === false ) {
                throw new InvalidCacheArgumentException('Invalid key provided');
            }
        });

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) {

            $values = array_combine($keys, array_map($keys, function($key) {
                return new Item($key);
            }));

        } else {

            if (version_compare(phpversion(), '7.0.0', '<')) {
                $values = $this->getInstance()->getMulti($keys, $null = null, MemcachedInstance::GET_PRESERVE_ORDER);
            } else {
                $values = $this->getInstance()->getMulti($keys, MemcachedInstance::GET_PRESERVE_ORDER);
            }

            array_walk($values, function ($value, $key) {
                if ( is_null($value) ) return new Item($key);
                $item = new Item($key, true);
                return $item->set(unserialize($value));
            });

        }

        return $items->merge($values);

    }

    public function hasItem($key) {

        $item = $this->getItem($key);

        return $item->isHit();

    }

    public function clear() {

        return $this->getInstance()->flush();

    }

    public function clearNamespace() {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        return $this->getInstance()->delete($namespace);

    }

    public function deleteItem($key) {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        $shadowName = "$namespace-$key";

        return $this->getInstance()->delete($shadowName);

    }

    public function deleteItems(array $keys) {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        $shadow_keys = array_map( function($key) use ($namespace) {
            return "$namespace-$key";
        }, $keys);

        $result = $this->getInstance()->deleteMulti($shadow_keys);

        return count(array_diff(array_unique($result), [true])) === 0;

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

        return $this->getInstance()->set($shadowName, serialize($item->getRaw()), $ttl);

    }

    public function getStats() {

        $stats = $this->getInstance()->getStats();

        $objects = 0;

        foreach ($stats as $key => $value) {

            $objects = max($objects, $value['curr_items']);

        }

        return new EnhancedCacheItemPoolStats('memcached', $this->getState(), $objects, $stats);

    }

    public function test() {

        return $this->getMemcachedStatus();

    }

    /**
     * Set namespace key
     *
     * @return  mixed
     */
    private function setNamespaceKey() {

        $uId = self::getUniqueId();

        return $this->getInstance()->set($this->getNamespace(), $uId, 0) === false ? false : $uId;

    }

    /**
     * Get namespace key
     *
     * @return  string
     */
    private function getNamespaceKey() {

        return $this->getInstance()->get($this->getNamespace());

    }

    private function getMemcachedStatus() {

        if ( sizeof($this->getInstance()->getServerList()) > 0 ) {

            $this->setState(self::CACHE_SUCCESS);

            return true;

        }

        $this->setState(self::CACHE_ERROR, 'No memcached server available');

        return false;

    }

    private static function checkMemcachedAvailability() {

        if ( class_exists('Memcached') === false ) throw new CacheException("ext-memcached not available");

    }

}
