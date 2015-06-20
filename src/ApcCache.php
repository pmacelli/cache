<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
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

class ApcCache extends CacheObject implements CacheInterface {

    public function __construct() {

        if ( self::getApcStatus() === false ) {

            $this->raiseError("Apc extension not available, disabling cache administratively");

            $this->disable();

        } else {

            try {
            
                parent::__construct();
                
            }
            
            catch ( CacheException $ce ) {
                
                throw $ce;
                
            }

        }

    }

    public function set($name, $data, $ttl=null) {

        if ( empty($name) ) {
            
            throw new CacheException("Name of object cannot be empty");
            
        }
        
        if ( is_null($data) ) {
            
            throw new CacheException("Object content cannot be null");
            
        }

        if ( !$this->isEnabled() ) return false;

        try {
            
            $this->setTtl($ttl);

            $namespace = $this->getNamespaceKey();

            if ( $namespace === false ) $namespace = $this->setNamespaceKey();

            if ( $namespace === false ) {

                $this->raiseError("Error writing cache (APC), exiting gracefully");

                $return = false;

            } else {

                $shadowName = $namespace."-".md5($name);
            
                $shadowTtl = $this->ttl;

                $return = apc_store($shadowName, $data, $shadowTtl);

                if ( $return === false ) {

                    $this->raiseError("Error writing cache (APC), exiting gracefully");

                }

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $return;

    }

    public function get($name) {

        if ( empty($name) ) {
            
            throw new CacheException("Name of object cannot be empty");
            
        }

        if ( !$this->isEnabled() ) return null;

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) {

            $return = null;

        } else {

            $shadowName = $namespace."-".md5($name);

            $return = apc_fetch($shadowName, $success);

            if ( $success === false ) {

                $this->raiseError("Error reading cache (APC), exiting gracefully");

                $return = null;

            }

        }

        return $return;

    }

    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return true;

        if ( empty($name) ) {

            $delete = apc_delete($this->getNamespace());

        } else {

            $delete = apc_delete($namespace."-".md5($name));

        }

        if ( $delete === false ) {

            $this->raiseError("Error deleting cache (APC), exiting gracefully");

        }


        return $delete;

    }

    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $result = apc_clear_cache("user");

        return $result;

    }

    public function status() {

        $stats = apc_cache_info("user",true);

        $objects = $stats["num_entries"];

        return array(
            "provider"  => "apc",
            "enabled"   => $this->isEnabled(),
            "objects"   => $objects,
            "options"   => $stats
        );

    }

    private function setNamespaceKey() {

        $uId = self::getUniqueId();

        $return = apc_store($this->getNamespace(), $uId, 0);

        return $return === false ? false : $uId;

    }

    private function getNamespaceKey() {

        return apc_fetch($this->getNamespace());

    }

    static private function getUniqueId() {

        return substr(md5(uniqid(rand(), true)), 0, 64);

    }

    static private function getApcStatus() {

        return ( ( extension_loaded('apc') OR extension_loaded('apc') ) AND ini_get('apc.enabled') );

    }

}