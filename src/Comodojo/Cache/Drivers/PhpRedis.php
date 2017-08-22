<?php namespace Comodojo\Cache\Drivers;

use \Comodojo\Cache\Traits\InstanceTrait;
use \Comodojo\Foundation\Utils\UniqueId;
use \RedisException;
use \Redis;
use \Exception;

/**
 * memcached provider
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

class PhpRedis extends AbstractDriver {

    use InstanceTrait;

    const DRIVER_NAME = "php-redis";

    private $connection_parameters;

    public function __construct(array $configuration) {

        if ( class_exists('Redis') === false ) throw new Exception("ext-redis not available");

        $instance = new Redis();

        $this->connection_parameters = [$configuration['server'], $configuration['port'], $configuration['timeout']];

        $instance->connect(...$this->connection_parameters);

        if ( !empty($configuration['password']) ) {
            $instance->auth($configuration['password']);
        }

        $this->setInstance($instance);

    }

    public function test() {

        $instance = $this->getInstance();

        try {

            $instance->ping();
            return true;

        } catch (RedisException $re) {

            // may be a connection error, try to reconnect first
            if ( $instance->connect(...$this->connection_parameters) === false ) {
                return false;
            } else {
                return true;
            }

        }

    }

    public function get($key, $namespace) {

        try {

            $scope = $this->getNamespaceKey($namespace);

            if ( $scope === false ) return null;

            $shadowName = "$scope-$key";

            $instance = $this->getInstance();

            $item = $instance->get($shadowName);

        } catch (RedisException $re) {

            throw $re;

        }

        return $item === false ? null : $item;

    }

    public function set($key, $namespace, $value, $ttl = null) {

        try {

            if ( $ttl == null ) $ttl = 0;

            $scope = $this->getNamespaceKey($namespace);

            if ( $scope === false ) $scope = $this->setNamespaceKey($namespace);

            if ( $scope === false ) return false;

            $shadowName = "$scope-$key";

            $instance = $this->getInstance();

            if ( $ttl == 0 ) {
                $return = $instance->set($shadowName, $value);
            } else {
                $return = $instance->setex($shadowName, $ttl, $value);
            }

        } catch (RedisException $re) {

            throw $re;

        }

        return (bool) $return;

    }

    public function delete($key, $namespace) {

        try {

            $scope = $this->getNamespaceKey($namespace);

            if ( $scope === false ) return false;

            $shadowName = "$scope-$key";

            return (bool) $this->getInstance()->delete($shadowName);

        } catch (RedisException $re) {

            throw $re;

        }

    }

    public function clear($namespace = null) {

        try {

            if ( $namespace == null ) {

                return (bool) $this->getInstance()->flushDB();

            } else {

                $scope = $this->getNamespaceKey($namespace);

                if ( $scope === false ) return false;

                return (bool) $this->getInstance()->delete($namespace);

            }

        } catch (RedisException $re) {

            throw $re;

        }

    }

    // TODO: write a better getMultiple using mGet
    public function getMultiple(array $keys, $namespace) {

        $result = [];

        foreach ( $keys as $key ) {
            $result[$key] = $this->get($key, $namespace);
        }

        return $result;

    }

    // TODO: write a better setMultiple using mSet
    public function setMultiple(array $key_values, $namespace, $ttl = null) {

        $result = [];

        foreach ( $key_values as $key => $value ) {
            $result[] = $this->set($key, $namespace, $value, $ttl);
        }

        return !in_array(false, $result);

    }

    // TODO: write a better deleteMultiple using delete([])
    public function deleteMultiple(array $keys, $namespace) {

        $result = [];

        foreach ( $keys as $key ) {
            $result[] = $this->delete($key, $namespace);
        }

        return !in_array(false, $result);

    }

    public function has($key, $namespace) {

        try {

            $scope = $this->getNamespaceKey($namespace);

            if ( $scope === false ) $scope = $this->setNamespaceKey($namespace);

            if ( $scope === false ) return false;

            $shadowName = "$scope-$key";

            return (bool) $this->getInstance()->exists($shadowName);

        } catch (RedisException $re) {

            throw $re;

        }

    }

    public function stats() {

        $instance = $this->getInstance();

        try {

            $objects = $instance->dbSize();
            $stats = $instance->info();

        } catch (RedisException $re) {

            throw $re;

        }

        return ['objects' => $objects, 'stats' => $stats];

    }

    /**
     * Set namespace key
     *
     * @return  mixed
     */
    private function setNamespaceKey($namespace) {

        $uId = UniqueId::generate(64);

        $return = $this->getInstance()->set($namespace, $uId);

        return $return === false ? false : $uId;

    }

    /**
     * Get namespace key
     *
     * @return  string
     */
    private function getNamespaceKey($namespace) {

        return $this->getInstance()->get($namespace);

    }

}
