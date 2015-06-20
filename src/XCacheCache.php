<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * XCache cache class
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

class XCacheCache extends CacheObject implements CacheInterface {

    public function __construct() {

        if ( self::getXCacheStatus() === false ) {

            $this->raiseError("XCache extension not available, disabling cache administratively");

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

        $this->resetErrorState();

        try {
            
            $this->setTtl($ttl);

            $shadowName = $this->getNamespace()."-".md5($name);
            
            $shadowTtl = $this->ttl;

            $shadowData = serialize($data);

            $return = xcache_set($shadowName, $shadowData, $shadowTtl);

            if ( $return === false ) {

                $this->raiseError("Error writing cache (XCache), exiting gracefully");

                $this->setErrorState();

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

        $this->resetErrorState();

        $shadowName = $this->getNamespace()."-".md5($name);

        $return = xcache_get($shadowName);

        if ( $return === false ) {

            $this->raiseError("Error reading cache (XCache), exiting gracefully");

            $this->setErrorState();

        }

        return is_null($return) ? null : unserialize($return);

    }

    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        if ( empty($name) ) {

            $delete = xcache_unset_by_prefix($this->getNamespace());

        } else {

            $delete = xcache_unset($this->getNamespace()."-".md5($name));

        }

        if ( $delete === false ) {

            $this->raiseError("Error writing cache (XCache), exiting gracefully");

            $this->setErrorState();

        }


        return $delete;

    }

    public function flush() {

        if ( !$this->isEnabled() ) return false;

        xcache_clear_cache(XC_TYPE_VAR, -1);

        return true;

    }

    public function status() {

        return array(
            "provider"  => "xcache",
            "enabled"   => $this->isEnabled(),
            "objects"   => xcache_count(XC_TYPE_VAR),
            "options"   => array()
        );

    }

    static private function getXCacheStatus() {

        return ( extension_loaded('xcache') AND function_exists("xcache_get") );

    }

}