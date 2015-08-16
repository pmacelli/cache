<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheObject\CacheObject;
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

class ApcCache extends CacheObject {

    /**
     * Class constructor
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct( \Monolog\Logger $logger=null ) {

        try {
            
            parent::__construct( $logger );
            
        }
        
        catch ( CacheException $ce ) {
            
            throw $ce;
            
        }

        if ( self::getApcStatus() === false ) {

            $this->raiseError("Apc extension not available, disabling cache administratively");

            $this->disable();

        }

    }

    /**
     * Set cache element
     *
     * This method will throw only logical exceptions.
     * In case of failures, it will return a boolean false.
     *
     * @param   string  $name    Name for cache element
     * @param   mixed   $data    Data to cache
     * @param   int     $ttl     Time to live
     *
     * @return  bool
     * @throws \Comodojo\Exception\CacheException
     */
    public function set($name, $data, $ttl=null) {

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

                $this->raiseError("Error writing cache (APC), exiting gracefully");

                $this->setErrorState();

                $return = false;

            } else {

                $shadowName = $namespace."-".md5($name);
            
                $shadowTtl = $this->ttl;

                $return = apc_store($shadowName, $data, $shadowTtl);

                if ( $return === false ) {

                    $this->raiseError("Error writing cache (APC), exiting gracefully");

                    $this->setErrorState();

                }

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $return;

    }

    /**
     * Get cache element
     *
     * This method will throw only logical exceptions.
     * In case of failures, it will return a null value.
     * In case of cache not found, it will return a null value.
     *
     * @param   string  $name    Name for cache element
     *
     * @return  mixed
     * @throws \Comodojo\Exception\CacheException
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

            $success=null;

            $return = apc_fetch($shadowName, $success);

            if ( $success === false ) {

                $this->raiseError("Error reading cache (APC), exiting gracefully");

                $this->setErrorState();

                $return = null;

            }

        }

        return $return;

    }

    /**
     * Delete cache object (or entire namespace if $name is null)
     *
     * This method will throw only logical exceptions.
     * In case of failures, it will return a boolean false.
     *
     * @param   string  $name    Name for cache element
     *
     * @return  bool
     * @throws \Comodojo\Exception\CacheException
     */
    public function delete($name=null) {

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

            $this->raiseError("Error deleting cache (APC), exiting gracefully");

            $this->setErrorState();

        }

        return $delete;

    }

    /**
     * Clean cache objects in all namespaces
     *
     * This method will throw only logical exceptions.
     *
     * @return  bool
     */
    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $result = apc_clear_cache("user");

        return $result;

    }

    /**
     * Get cache status
     *
     * @return  array
     */
    public function status() {

        if ( !$this->isEnabled() ) return array(
            "provider"  => "apc",
            "enabled"   => false,
            "objects"   => null,
            "options"   => array()
        );

        $stats = apc_cache_info("user",true);

        if ( isset($stats["num_entries"]) ) {

            $objects = $stats["num_entries"];

        } else {

            // some APC extensions do not return the "num_entries", so let's try to calculate it
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

        return ( ( extension_loaded('apc') OR extension_loaded('apc') ) AND ini_get('apc.enabled') );

    }

}
