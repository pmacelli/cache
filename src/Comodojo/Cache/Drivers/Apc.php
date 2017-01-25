<?php namespace Comodojo\Cache\Drivers;

use \Comodojo\Cache\Components\UniqueId;
use \Exception;

/**
 * Apcu provider
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

class Apc extends AbstractDriver {

    const DRIVER_NAME = "apc";

    public function __construct(array $configuration = []) {

        if ( extension_loaded('apc') === false ) throw new Exception("ext-apc not available");

        // In cli, apcu SHOULD NOT use the request time for cache retrieve/invalidation.
        // This is because in cli the request time is allways the same.
        if ( php_sapi_name() === 'cli' ) {
            ini_set('apc.use_request_time', 0);
        }

    }

    public function test() {

        return (bool) ini_get('apc.enabled');

    }

    public function get($key, $namespace) {

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return null;

        $shadowName = "$scope-$key";

        $item = apc_fetch($shadowName, $success);

        return $success === false ? null : $item;

    }

    public function set($key, $namespace, $value, $ttl = null) {

        if ( $ttl == null ) $ttl = 0;

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) $scope = $this->setNamespaceKey($namespace);

        if ( $scope === false ) return false;

        $shadowName = "$scope-$key";

        return apc_store($shadowName, $value, $ttl);

    }

    public function delete($key, $namespace) {

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return false;

        $shadowName = "$scope-$key";

        return apc_delete($shadowName);

    }

    public function clear($namespace = null) {

        if ( $namespace == null ) {

            return apc_clear_cache("user");

        } else {

            $scope = $this->getNamespaceKey($namespace);

            if ( $scope === false ) return false;

            return apc_delete($namespace);

        }

    }

    public function getMultiple(array $keys, $namespace) {

        $keypad = array_combine($keys, array_fill(0, count($keys), null));

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return $keypad;

        $keyscope = array_map(function($key) use($scope) {
            return "$scope-$key";
        }, $keys);

        $data = apc_fetch($keyscope, $success);

        $return = [];

        foreach ($data as $scoped_key => $value) {
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

        foreach ($key_values as $key => $value) {
            $shadowNames["$scope-$key"] = $value;
        }

        $data = apc_store($shadowNames, null, $ttl);

        return empty($data) ? true : false;

    }

    public function deleteMultiple(array $keys, $namespace) {

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return false;

        $shadowNames = array_map(function($key) use($scope) {
            return "$scope-$key";
        }, $keys);

        $delete = apc_delete($shadowNames);

        return empty($delete) ? true : false;

    }

    public function has($key, $namespace) {

        $scope = $this->getNamespaceKey($namespace);

        if ( $scope === false ) return false;

        return apc_exists("$scope-$key");

    }

    public function stats() {

        return apc_cache_info("user", true);

    }

    /**
     * Set namespace key
     *
     * @return  mixed
     */
    private function setNamespaceKey($namespace) {

        $uId = UniqueId::get();

        return apc_store($namespace, $uId, 0) === false ? false : $uId;

    }

    /**
     * Get namespace key
     *
     * @return  string
     */
    private function getNamespaceKey($namespace) {

        return apc_fetch($namespace);

    }

}
