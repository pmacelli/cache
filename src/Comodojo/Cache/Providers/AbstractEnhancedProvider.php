<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Traits\StatefulTrait;
use \Comodojo\Cache\Traits\NamespaceTrait;
use \Comodojo\Cache\Interfaces\EnhancedCacheItemPoolInterface;
use \Comodojo\Cache\Item;
use \Comodojo\Cache\Components\KeyValidator;
use \Comodojo\Foundation\Utils\UniqueId;
use \Comodojo\Foundation\Utils\ClassProperties;
use \Psr\Log\LoggerInterface;
use \Psr\Cache\CacheItemInterface;
use \DateTime;
use \Comodojo\Exception\CacheException;
use \Comodojo\Exception\InvalidCacheArgumentException;
use \Exception;

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

abstract class AbstractEnhancedProvider
    extends AbstractProvider
    implements EnhancedCacheItemPoolInterface {

    use StatefulTrait;
    use NamespaceTrait;

    protected $driver;

    protected $default_properties = [];

    protected $properties;

    private $queue = [];

    public function __construct(array $properties = [], LoggerInterface $logger = null) {

        parent::__construct($logger);

        $this->properties = ClassProperties::create($this->default_properties)->merge($properties);

        $this->setId(UniqueId::generate(64));

    }

    abstract public function getStats();

    public function getProperties() {

        return $this->properties;

    }

    public function getItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        try {

            $data = $this->driver->get($key, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = null;

        }

        if ( $data === null ) return new Item($key);

        $item = new Item($key, true);

        return $item->set(unserialize($data));

    }

    public function hasItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        try {

            $data = $this->driver->has($key, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function clear() {

        try {

            $data = $this->driver->clear();

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function clearNamespace() {

        try {

            $data = $this->driver->clear($this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function deleteItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        try {

            $data = $this->driver->delete($key, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function save(CacheItemInterface $item) {

        $ttl = $item->getTtl();

        if ( $ttl < 0 ) return false;

        try {

            $data = $this->driver->set($item->getKey(), $this->getNamespace(), serialize($item->getRaw()), $ttl);

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function test() {

        if ( $this->driver->test() ) {

            $this->setState(self::CACHE_SUCCESS);

            return true;

        }

        $error = $this->driver->getName()." driver unavailable, disabling provider ".$this->getId()." administratively";

        $this->logger->error($error);

        $this->setState(self::CACHE_ERROR, $error);

        return false;

    }

    public function getItems(array $keys = []) {

        if ( empty($keys) ) return [];

        $result = [];

        try {

            $data = $this->driver->getMultiple($keys, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = array_combine($keys, array_fill(0, count($keys), null));

        }

        foreach ( $data as $key => $value ) {

            if ( $value == null ) {
                $result[$key] = new Item($key);
            } else {
                $result[$key] = new Item($key, true);
                $result[$key]->set(unserialize($value));
            }

        }

        return $result;

    }

    public function deleteItems(array $keys) {

        try {

            $data = $this->driver->deleteMultiple($keys, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function saveDeferred(CacheItemInterface $item) {

        $this->checkQueueNamespace(true);

        $namespace = $this->getNamespace();

        $this->queue[$namespace][$item->getKey()] = $item;

        return true;

    }

    public function commit() {

        $result = [];

        $active_namespace = $this->getNamespace();

        foreach ( $this->queue as $namespace => $queue ) {

            $this->setNamespace($namespace);

            foreach ( $queue as $key => $item ) {

                $result[] = $this->save($item);

            }

        }

        $this->queue = [];

        $this->setNamespace($active_namespace);

        return in_array(false, $result) ? false : true;

    }

    private function checkQueueNamespace($create = false) {

        $namespace = $this->getNamespace();

        if ( array_key_exists($namespace, $this->queue) ) {
            return true;
        } else if ( $create ) {
            $this->queue[$namespace] = [];
            return true;
        } else {
            return false;
        }

    }

}
