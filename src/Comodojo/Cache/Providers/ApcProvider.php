<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Components\AbstractProvider;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * Apc cache class
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

class ApcProvider extends AbstractProvider {

    /**
     * Class constructor
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct(LoggerInterface $logger = null) {

        parent::__construct($logger);
        
        if ( self::getApcStatus() === false ) {

            $this->logger->error("Apc extension not available, disabling ApcProvider administratively");

            $this->disable();

        }

    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $data, $ttl = null) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");

        if ( is_null($data) ) throw new CacheException("Object content cannot be null");

        // simply return false if cache is disabled
        if ( !$this->isEnabled() ) return false;

        // reset error state, just in case
        $this->resetErrorState();

        try {
            
            $this->setTtl($ttl);

            $namespace = $this->getNamespaceKey();

            if ( $namespace === false ) $namespace = $this->setNamespaceKey();

            // if namespace is still false, raise an error and exit gracefully
            if ( $namespace === false ) {

                $this->logger->error("Error writing cache (APC), exiting gracefully");

                $this->setErrorState();

                $return = false;

            } else {

                $shadowName = $namespace."-".md5($name);
            
                $shadowTtl = $this->ttl;

                $return = apc_store($shadowName, $data, $shadowTtl);

                if ( $return === false ) {

                    $this->logger->error("Error writing cache (APC), exiting gracefully");

                    $this->setErrorState();

                }

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $return;

    }

    /**
     * {@inheritdoc}
     */
    public function get($name) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");

        if ( !$this->isEnabled() ) return null;

        $this->resetErrorState();

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) {

            $return = null;

        } else {

            $shadowName = $namespace."-".md5($name);

            $success = null;

            $return = apc_fetch($shadowName, $success);

            if ( $success === false ) {

                $this->logger->error("Error reading cache (APC), exiting gracefully");

                $this->setErrorState();

                $return = null;

            }

        }

        return $return;

    }

    /**
     * {@inheritdoc}
     */
    public function delete($name = null) {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return true;

        if ( empty($name) ) {

            $delete = apc_delete($this->getNamespace());

        } else {

            $delete = apc_delete($namespace."-".md5($name));

        }

        if ( $delete === false ) {

            $this->logger->error("Error deleting cache (APC), exiting gracefully");

            $this->setErrorState();

        }

        return $delete;

    }

    /**
     * {@inheritdoc}
     */
    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $result = apc_clear_cache("user");

        return $result;

    }

    /**
     * {@inheritdoc}
     */
    public function status() {

        if ( !$this->isEnabled() ) return array(
            "provider"  => "apc",
            "enabled"   => false,
            "objects"   => null,
            "options"   => array()
        );

        $stats = apc_cache_info("user", true);

        if ( isset($stats["num_entries"]) ) {

            $objects = $stats["num_entries"];

        } else {

            //  in some APC extensions the "num_entries" field is not available; let's try to calculate it
            $stats_2 = apc_cache_info("user");

            $objects = sizeof($stats_2["cache_list"]);

        }

        return array(
            "provider"  => "apc",
            "enabled"   => $this->isEnabled(),
            "objects"   => intval($objects),
            "options"   => $stats
        );

    }

    /**
     * Set namespace key
     *
     * @return  mixed
     */
    private function setNamespaceKey() {

        $uId = self::getUniqueId();

        $return = apc_store($this->getNamespace(), $uId, 0);

        return $return === false ? false : $uId;

    }

    /**
     * Get namespace key
     *
     * @return  string
     */
    private function getNamespaceKey() {

        return apc_fetch($this->getNamespace());

    }

    /**
     * Check APC availability
     *
     * @return  bool
     */
    private static function getApcStatus() {

        return ((extension_loaded('apc') || extension_loaded('apc')) && ini_get('apc.enabled'));

    }

}
