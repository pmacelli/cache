<?php namespace Comodojo\Cache\Drivers;

use \Comodojo\Cache\Traits\InstanceTrait;
use \Comodojo\Foundation\Utils\UniqueId;
use \Memcached as MemcachedInstance;
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

class Memcached extends AbstractDriver {

    use InstanceTrait;

    const DRIVER_NAME = "memcached";

    public function __construct(array $configuration) {

        if ( class_exists('Memcached') === false ) throw new Exception("ext-memcached not available");

        $instance = new MemcachedInstance($configuration['persistent_id']);

        $instance->addServer(
            $configuration['server'],
            $configuration['port'],
            $configuration['weight']
        );

        if ( !empty($configuration['username']) && !empty($configuration['password']) ) {
            $instance->setOption(MemcachedInstance::OPT_BINARY_PROTOCOL, true);
            $instance->setSaslAuthData($configuration['username'], $configuration['password']);
        }

        $this->setInstance($instance);

    }

    public function test() {

        return sizeof($this->getInstance()->getServerList()) > 0 && $this->ping();

    }

    public function ping() {

        // try to read a fake value from cache and check it's return code
        $instance = $this->getInstance();
        $instance->get('cache-internals');
        $code = $instance->getResultCode();

        // check if code represents a failure
        // $code != [MEMCACHED_SUCCESS, MEMCACHED_NOTFOUND]
        return in_array($code, [0, 16]);

    }

    public function get($key, $namespace) {

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return null;

        $shadowName = "$scope-$key";

        $instance = $this->getInstance();

        $item = $instance->get($shadowName);

        return $instance->getResultCode() == MemcachedInstance::RES_NOTFOUND ? null : $item;

    }

    public function set($key, $namespace, $value, $ttl = null) {

        if ( $ttl == null ) $ttl = 0;

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) $scope = $this->setNamespaceKey($namespace);

        if ( $scope === false ) return false;

        $shadowName = "$scope-$key";

        return $this->getInstance()->set($shadowName, $value, $ttl);

    }

    public function delete($key, $namespace) {

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return false;

        $shadowName = "$scope-$key";

        return $this->getInstance()->delete($shadowName);

    }

    public function clear($namespace = null) {

        if ( $namespace == null ) {

            return $this->getInstance()->flush();

        } else {

            $scope = $this->getNamespaceKey($namespace);

            if ( $scope === false ) return false;

            return $this->getInstance()->delete($namespace);

        }

    }

    public function getMultiple(array $keys, $namespace) {

        if ( empty($keys) ) return [];

        $keypad = array_combine($keys, array_fill(0, count($keys), null));

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return $keypad;

        $keyscope = array_map(function($key) use($scope) {
            return "$scope-$key";
        }, $keys);

        if ( version_compare(phpversion(), '7.0.0', '<') ) {
            $data = $this->getInstance()->getMulti($keyscope, $null = null, MemcachedInstance::GET_PRESERVE_ORDER);
        } else {
            $data = $this->getInstance()->getMulti($keyscope, MemcachedInstance::GET_PRESERVE_ORDER);
        }

        $return = [];

        foreach ( $data as $scoped_key => $value ) {
            $key = substr($scoped_key, strlen("$scope-"));
            $return[$key] = $value;
        }

        return array_replace($keypad, $return);

    }

    public function setMultiple(array $key_values, $namespace, $ttl = null) {

        if ( $ttl == null ) $ttl = 0;

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) $scope = $this->setNamespaceKey($namespace);

        if ( $scope === false ) return false;

        $shadowNames = [];

        foreach ( $key_values as $key => $value ) {
            $shadowNames["$scope-$key"] = $value;
        }

        return $this->getInstance()->setMulti($shadowNames, $ttl);

    }

    public function deleteMultiple(array $keys, $namespace) {

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return false;

        $shadowNames = array_map(function($key) use($scope) {
            return "$scope-$key";
        }, $keys);

        $delete = $this->getInstance()->deleteMulti($shadowNames);

        return count(array_diff(array_unique($delete), [true])) === 0;

    }

    public function has($key, $namespace) {

        return $this->get($key, $namespace) === null ? false : true;

    }

    public function stats() {

        return $this->getInstance()->getStats();

    }

    /**
     * Set namespace key
     *
     * @return  mixed
     */
    private function setNamespaceKey($namespace) {

        $uId = UniqueId::generate(64);

        return $this->getInstance()->set($namespace, $uId, 0) === false ? false : $uId;

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
