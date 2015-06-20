<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Comodojo\Exception\CacheException;

/**
 * File cache class
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

class FileCache extends CacheObject implements CacheInterface {
 
    private $cache_folder = null;
 
    public function __construct( $cache_folder=null ) {

        if ( !empty($cache_folder) AND is_string($cache_folder) ) {
            
            $this->cache_folder = $cache_folder[strlen($cache_folder)-1] == "/" ? $cache_folder : ( $cache_folder . "/" );
            
        } else if ( defined("COMODOJO_CACHE_FOLDER") ) {
            
            $folder = COMODOJO_CACHE_FOLDER;
            
            $this->cache_folder = $folder[strlen($folder)-1] == "/" ? $folder : ( $folder . "/" );
            
        } else {
        
            throw new CacheException("Invalid or unspecified cache folder");
            
        }

        if ( self::checkCacheFolder($this->cache_folder) === false ) {

            $this->raiseError("Cache folder not writeable, disabling cache administratively");

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
        
            $shadowName = $this->cache_folder . md5($name)."-".$this->getNamespace();
            
            $shadowData = serialize($data);
    
            $shadowTtl = $this->getTime() + $this->ttl;
        
            if ( self::checkXattrSupport() AND self::checkXattrFilesystemSupport($this->cache_folder) ) {

                $return = $this->setXattr($shadowName, $shadowData, $shadowTtl);

            } else {

                $return = $this->setGhost($shadowName, $shadowData, $shadowTtl);

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

        $shadowName = $this->cache_folder . md5($name)."-".$this->getNamespace();
    
        if ( self::checkXattrSupport() AND self::checkXattrFilesystemSupport($this->cache_folder) ) {

            $return = $this->getXattr($shadowName, $this->getTime());

        } else {

            $return = $this->getGhost($shadowName, $this->getTime());

        }

        return is_null($return) ? $return : unserialize($return);

    }
    
    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        $return = true;

        if ( is_null($name) ) {

            $filesList = glob($this->cache_folder."*-".$this->getNamespace().".{cache,expire}", GLOB_BRACE);

        } else {

            $filesList = glob($this->cache_folder.md5($name)."-".$this->getNamespace().".{cache,expire}", GLOB_BRACE);

        }

        foreach ($filesList as $file) {

            if ( unlink($file) === false ) {

                $this->raiseError("Failed to unlink cache file (File), exiting gracefully", pathinfo($file));

                $this->setErrorState();
                
                $return = false;

            }

        }

        return $return;

    }

    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        $return = true;

        $filesList = glob($this->cache_folder."*.{cache,expire}", GLOB_BRACE);

        foreach ($filesList as $file) {

            if ( unlink($file) === false ) {

                $this->raiseError("Failed to unlink whole cache (File), exiting gracefully", pathinfo($file));

                $this->setErrorState();

                $return = false;

            }

        }

        return $return;

    }

    public function status() {

        $filesList = glob($this->cache_folder."*.cache");

        if ( self::checkXattrSupport() ) {

            $options = array(
                "xattr_supported"   =>  true,
                "xattr_enabled"     =>  $this->checkXattrFilesystemSupport($this->cache_folder)
            );

        } else {

            $options = array(
                "xattr_supported"   =>  false,
                "xattr_enabled"     =>  false
            );

        }

        return array(
            "provider"  => "file",
            "enabled"   => $this->isEnabled(),
            "objects"   => count($filesList),
            "options"   => $options
        );

    }

    private function setXattr($name, $data, $ttl) {

        $cacheFile = $name . ".cache";

        $cached = file_put_contents($cacheFile, $data, LOCK_EX);

        if ( $cached === false ) {

            $this->raiseError("Error writing cache object (File), exiting gracefully", pathinfo($cacheFile));

            $this->setErrorState();

            return false;

        }

        $tagged = xattr_set($cacheFile, "EXPIRE", $ttl, XATTR_DONTFOLLOW);

        if ( $tagged === false ) {

            $this->raiseError("Error writing cache ttl (File) (XATTR), exiting gracefully", pathinfo($cacheFile));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    private function setGhost($name, $data, $ttl) {

        $cacheFile = $name . ".cache";

        $cacheGhost = $name . ".expire";

        $cached = file_put_contents($cacheFile, $data, LOCK_EX);
        
        if ( $cached === false ) {

            $this->raiseError("Error writing cache object (File), exiting gracefully", pathinfo($cacheFile));

            $this->setErrorState();

            return false;

        }

        $tagged = file_put_contents($cacheGhost, $ttl, LOCK_EX);

        if ( $tagged === false ) {

            $this->raiseError("Error writing cache ttl (File) (GHOST), exiting gracefully", pathinfo($cacheGhost));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    private function getXattr($name, $time) {

        $cacheFile = $name . ".cache";

        if ( file_exists($cacheFile) ) {

            $expire = xattr_get($cacheFile, "EXPIRE", XATTR_DONTFOLLOW);

            if ( $expire === false ) {
                
                $this->raiseError("Error reading cache ttl (File) (XATTR), exiting gracefully", pathinfo($cacheFile));

                $this->setErrorState();

                $return = null;

            } else if ( $expire < $time ) {
                
                $return = null;
                
            } else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) {
                    
                    $this->raiseError("Error reading cache content (File), exiting gracefully", pathinfo($cacheFile));

                    $this->setErrorState();
                    
                    $return = null;
                    
                } else {
                    
                    $return = $data;
                    
                }
                
            }

        } else {
            
            $return = null;
        
        }
        
        return $return;

    }

    private function getGhost($name, $time) {

        $cacheFile = $name . ".cache";

        $cacheGhost = $name . ".expire";

        if ( file_exists($cacheFile) ) {

            $expire = file_get_contents($cacheGhost);

            if ( $expire === false ) {

                $this->raiseError("Error reading cache ttl (File) (GHOST), exiting gracefully", pathinfo($cacheGhost));
                
                $return = null;

            } else if ( intval($expire) < $time ) {
                
                $return = null;
                
            } else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) {
                    
                    $this->raiseError("Error reading cache content (File), exiting gracefully", pathinfo($cacheFile));
                    
                    $return = null;
                    
                } else {
                    
                    $return = $data;
                    
                }
                
            }

        } else {
            
            $return = null;
        
        }
        
        return $return;

    }
    
    static private function checkCacheFolder($folder) {
        
        return is_writable( $folder );
        
    }
    
    static private function checkXattrSupport() {
        
        return function_exists( "xattr_supported" );
        
    }
    
    static private function checkXattrFilesystemSupport($folder) {
        
        return xattr_supported( $folder );
        
    }
    
}