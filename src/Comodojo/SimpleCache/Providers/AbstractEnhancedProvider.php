<?php namespace Comodojo\SimpleCache\Providers;

use \Comodojo\Cache\Traits\StatefulTrait;
use \Comodojo\Cache\Traits\NamespaceTrait;
use \Comodojo\SimpleCache\Interfaces\EnhancedSimpleCacheInterface;
use \Comodojo\Cache\Components\UniqueId;
use \Psr\Log\LoggerInterface;
use \Comodojo\Cache\Components\KeyValidator;
use \DateTime;
use \DateInterval;
use \Traversable;
use \Comodojo\Exception\SimpleCacheException;
use \Comodojo\Exception\InvalidSimpleCacheArgumentException;
use \Exception;

/**
 * Abstract stateful provider implementation
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

abstract class AbstractEnhancedProvider
    extends AbstractProvider
    implements EnhancedSimpleCacheInterface {

    use StatefulTrait;
    use NamespaceTrait;

    protected $driver;

    private $queue = [];

    public function __construct(LoggerInterface $logger = null) {

        parent::__construct($logger);

        $this->setId(UniqueId::get())->test();

    }

    public function get($key, $default = null) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidSimpleCacheArgumentException('Invalid key provided');
        }

        try {

            $data = $this->driver->get($key, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = null;

        }

        if ( $data === null ) return $default;

        return unserialize($data);

    }

    public function set($key, $value, $ttl = null) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidSimpleCacheArgumentException('Invalid key provided');
        }

        if ( $value === null ) {
            throw new InvalidSimpleCacheArgumentException('Cannot cache a null value');
        }

        $real_ttl;

        if ( $ttl == null || $ttl == 0 ) $real_ttl = 0;
        else if ( $ttl instanceof DateInterval ) $real_ttl = $ttl->format('%s');
        else $real_ttl = intval($ttl);

        try {

            $data = $this->driver->set($key, $this->getNamespace(), serialize($value), $real_ttl);

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function delete($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidSimpleCacheArgumentException('Invalid key provided');
        }

        try {

            $data = $this->driver->delete($key, $this->getNamespace());

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

    public function getMultiple($keys, $default = null) {

        if ( !is_array($keys) && !($keys instanceof Traversable ) ) {
            throw new InvalidSimpleCacheArgumentException('Invalid keys provided');
        }

        foreach ($keys as $key) {
            if ( KeyValidator::validateKey($key) === false ) {
                throw new InvalidSimpleCacheArgumentException('Invalid key provided');
            }
        }

        try {

            $data = $this->driver->getMultiple($keys, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = array_combine($keys, array_fill(0, count($keys), null));

        }

        return array_map(function($value) use($default) {
            if ( $value === null ) return $default;
            return unserialize($value);
        }, $data);

    }

    public function setMultiple($values, $ttl = null) {

        if ( !is_array($values) && !($values instanceof Traversable ) ) {
            throw new InvalidSimpleCacheArgumentException('Invalid keys provided');
        }

        $real_values = [];

        foreach ($values as $key => $value) {
            if ( KeyValidator::validateKey($key) === false ) {
                throw new InvalidSimpleCacheArgumentException('Invalid key provided');
            }
            if ( $value === null ) {
                throw new InvalidSimpleCacheArgumentException('Cannot cache a null value');
            }
            $real_values[$key] = serialize($value);
        }

        $real_ttl;

        if ( $ttl == null || $ttl == 0 ) $real_ttl = 0;
        else if ( $ttl instanceof DateInterval ) $real_ttl = $ttl->format('%s');
        else $real_ttl = intval($ttl);

        try {

            $data = $this->driver->setMultiple($real_values, $this->getNamespace(), $real_ttl);

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function deleteMultiple($keys) {

        if ( !is_array($keys) && !($keys instanceof Traversable ) ) {
            throw new InvalidSimpleCacheArgumentException('Invalid keys provided');
        }

        foreach ($keys as $key) {
            if ( KeyValidator::validateKey($key) === false ) {
                throw new InvalidSimpleCacheArgumentException('Invalid key provided');
            }
        }

        try {

            $data = $this->driver->deleteMultiple($keys, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    public function has($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidSimpleCacheArgumentException('Invalid key provided');
        }

        try {

            $data = $this->driver->has($key, $this->getNamespace());

        } catch (Exception $e) {

            $this->setState(self::CACHE_ERROR, $e->getMessage());
            $data = false;

        }

        return $data;

    }

    abstract public function getStats();

    public function clearNamespace() {

        try {

            $data = $this->driver->clear($this->getNamespace());

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

}
