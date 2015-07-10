<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheObject\CacheObject;
use \Memcached;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * Memcached cache class
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

class MemcachedCache extends CacheObject {

    /**
     * Internal memcached handler
     *
     * @var \Memcached
     */
    private $instance = null;

    /**
     * Class constructor
     *
     * @param   string          $server         Server address (or IP)
     * @param   string          $port           (optional) Server port
     * @param   string          $weight         (optional) Server weight
     * @param   string          $persistent_id  (optional) Persistent id
     * @param   \Monolog\Logger $logger         Logger instance
     * 
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct( $server, $port=11211, $weight=0, $persistent_id=null, \Monolog\Logger $logger=null ) {

        if ( empty($server) ) throw new CacheException("Invalid or unspecified memcached server");

        if ( !is_null($persistent_id) AND !is_string($persistent_id) ) throw new CacheException("Invalid persistent id");

        if ( self::getMemcachedStatus() === false ) {

            $this->raiseError("Memcached extension not available, disabling cache administratively");

            $this->disable();
            
            return;

        }
        
        $this->instance = new Memcached($persistent_id);

        $port = filter_var($port, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 1,
                "max_range" => 65535,
                "default" => 11211 )
            )
        );

        $weight = filter_var($weight, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 0,
                "default" => 0 )
            )
        );

        $this->addServer($server, $port, $weight);

        if ( $this->isEnabled() ) {

            try {
            
                parent::__construct( $logger );
                
            }
            
            catch ( CacheException $ce ) {
                
                throw $ce;
                
            }

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
        
        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {
            
            $this->setTtl($ttl);

            $namespace = $this->getNamespaceKey();

            if ( $namespace === false ) $namespace = $this->setNamespaceKey();

            if ( $namespace === false ) {

                $this->raiseError("Error writing cache (Memcached), exiting gracefully", array(
                    "RESULTCODE" => $this->instance->getResultCode(),
                    "RESULTMESSAGE" => $this->instance->getResultMessage()
                ));

                $this->setErrorState();

                $return = false;

            } else {

                $shadowName = $namespace."-".md5($name);
            
                $shadowTtl = $this->getTime() + $this->ttl;

                $shadowData = serialize($data);
                
                $return = $this->instance->set($shadowName, $shadowData, $shadowTtl);

                if ( $return === false ) {

                    $this->raiseError("Error writing cache (Memcached), exiting gracefully", array(
                        "RESULTCODE" => $this->instance->getResultCode(),
                        "RESULTMESSAGE" => $this->instance->getResultMessage()
                    ));

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

            $return = false;

        } else {

            $shadowName = $namespace."-".md5($name);

            $return = $this->instance->get($shadowName);

            if ( $return === false AND $this->instance->getResultCode() != Memcached::RES_NOTFOUND ) {

                $this->raiseError("Error reading cache (Memcached), exiting gracefully", array(
                    "RESULTCODE" => $this->instance->getResultCode(),
                    "RESULTMESSAGE" => $this->instance->getResultMessage()
                ));

                $this->setErrorState();

            }

        }

        return $return === false ? null : unserialize($return);

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

            $delete = $this->instance->delete($this->getNamespace());

        } else {

            $delete = $this->instance->delete($namespace."-".md5($name));

        }

        if ( $delete === false ) {

            $this->raiseError("Error writing cache (Memcached), exiting gracefully", array(
                "RESULTCODE" => $this->instance->getResultCode(),
                "RESULTMESSAGE" => $this->instance->getResultMessage()
            ));

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
     * @throws \Comodojo\Exception\CacheException
     */
    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        $result = $this->instance->flush();

        if ( $result === false ) {

            $this->raiseError("Error flushing cache (Memcached), exiting gracefully", array(
                "RESULTCODE" => $this->instance->getResultCode(),
                "RESULTMESSAGE" => $this->instance->getResultMessage()
            ));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    /**
     * Get cache status
     *
     * @return  array
     */
    public function status() {

        if ( !$this->isEnabled() ) return array(
            "provider"  => "memcached",
            "enabled"   => false,
            "objects"   => null,
            "options"   => array()
        );

        $stats = $this->instance->getStats();

        $objects = 0;

        foreach ($stats as $key => $value) {
            
            $objects = max($objects, $value['curr_items']);

        }

        return array(
            "provider"  => "memcached",
            "enabled"   => $this->isEnabled(),
            "objects"   => intval($objects),
            "options"   => $stats
        );

    }

    /**
     * Get the current memcached instance
     *
     * @return  \Memcached
     */
    public final function getInstance() {

        return $this->instance;

    }

    /**
     * Set key for namespace
     *
     * @return  mixed
     */
    private function setNamespaceKey() {

        $uId = self::getUniqueId();

        $return = $this->instance->set($this->getNamespace(), $uId, 0);

        return $return === false ? false : $uId;

    }

    /**
     * Get key for namespace
     *
     * @return  string
     */
    private function getNamespaceKey() {

        return $this->instance->get($this->getNamespace());

    }

    /**
     * Add a Memcached server to stack
     *
     * @param   string          $server         Server address (or IP)
     * @param   string          $port           Server port
     * @param   string          $weight         Server weight
     */
    private function addServer($server, $port, $weight) {

        $status = $this->instance->addServer($server, $port, $weight);

        if ( $status === false ) {

            $this->raiseError("Error communicating with server", array(
                "RESULTCODE" => $this->instance->getResultCode(),
                "RESULTMESSAGE" => $this->instance->getResultMessage()
            ));

        }

        if ( sizeof($this->instance->getServerList()) == 0 ) {

            $this->raiseError("No available server, disabling cache administratively");

            $this->disable();

        } else {

            $this->enable();

        }

    }
    
    /**
     * Check Memcached availability
     *
     * @return  bool
     */
    static private function getMemcachedStatus() {
        
        return class_exists('Memcached');
        
    }

}
