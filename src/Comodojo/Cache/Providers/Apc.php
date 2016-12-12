<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Components\BasicCacheItemPoolTrait;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Comodojo\Cache\Components\KeyValidator;
use \Psr\Cache\CacheItemInterface;
use \Comodojo\Exception\CacheException;
use \Exception;
use \DateTime;

/**
 * Apc provider
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

class Apc extends AbstractEnhancedProvider {

    use BasicCacheItemPoolTrait;

    public function __construct(LoggerInterface $logger = null) {

        parent::__construct($logger);

        $this->getApcStatus();

        self::makeApcCliCompatible();

    }

    public function getItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return new Item($key);

        $shadowName = "$namespace-$key";

        $data = apc_fetch($shadowName, $success);

        if ( $data === false ) return new Item($key);

        $item = new Item($key, true);

        return $item->set($data);

    }

    public function hasItem($key) {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        $shadowName = "$namespace-$key";

        return apc_exists($shadowName);

    }

    public function clear() {

        return apc_clear_cache("user");

    }

    public function clearNamespace() {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        return apc_delete($namespace);

    }

    public function deleteItem($key) {

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return false;

        $shadowName = "$namespace-$key";

        return apc_delete($shadowName);

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

        return apc_store($shadowName, $item->getRaw(), $ttl);

    }

    public function getStats() {

        $info = apc_cache_info("user", true);

        $entries = isset($info['num_entries']) ? $info['num_entries'] : null;

        return new EnhancedCacheItemPoolStats('apc', $this->getState(), $entries, $info);

    }

    public function test() {

        return $this->getApcStatus();

    }

    /**
     * Check APC availability
     *
     * @return  bool
     */
    private function getApcStatus() {

        $apc = extension_loaded('apc');

        if ( $apc && ini_get('apc.enabled') ) {

            $this->setState(self::CACHE_SUCCESS);

            return true;

        }

        $error = "Apc extension not available, disabling provider ".$this->getId()." administratively";

        $this->logger->error($error);

        $this->setState(self::CACHE_ERROR, $error);

        return false;

    }

    /**
     * If in CLI, disable apc request time
     */
    private static function makeApcCliCompatible() {

        // In cli, apc SHOULD NOT use the request time for cache retrieve/invalidation.
        // This is because in cli the request time is allways the same.
        if ( php_sapi_name() === 'cli' ) {
            ini_set('apc.use_request_time', 0);
        }

    }

    /**
     * Set namespace key
     *
     * @return  mixed
     */
    private function setNamespaceKey() {

        $uId = self::getUniqueId();

        return apc_store($this->getNamespace(), $uId, 0) === false ? false : $uId;

    }

    /**
     * Get namespace key
     *
     * @return  string
     */
    private function getNamespaceKey() {

        return apc_fetch($this->getNamespace());

    }

}
