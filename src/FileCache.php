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
 
    public function __construct( $cache_folder=false ) {

        if ( $cache_folder !== false ) {
            
            $this->cache_folder = $cache_folder[strlen($cache_folder)-1] == "/" ? $cache_folder : ( $cache_folder . "/" );
            
        } else if ( defined("COMODOJO_CACHE_FOLDER") ) {
            
            $folder = COMODOJO_CACHE_FOLDER;
            
            $this->cache_folder = $folder[strlen($folder)-1] == "/" ? $folder : ( $folder . "/" );
            
        } else {
        
            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Invalid or unspecified cache folder");
            
            throw new CacheException("Invalid or unspecified cache folder");
            
        }
        
        try {
            
            self::checkCacheFolder($this->cache_folder);
            
        }
        
        catch ( CacheException $ce ) {
            
            throw $ce;
            
        }
        
        parent::__construct();

    }
    
    public function set($name, $data, $ttl=null) {

        if ( empty($name) ) {
            
            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Name of object cannot be empty");
            
            throw new CacheException("Name of object cannot be empty");
            
        }
        
        if ( empty($data) ) {
            
            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Object cannot be empty");
            
            throw new CacheException("Object cannot be empty");
            
        }
        
        try {
            
            $this->setTtl($ttl);
        
            $shadowName = $this->cache_folder . md5($name)."-".$this->getScope();
            
            $shadowData = serialize($data);
    
            $shadowTtl = $this->getTime() + $this->ttl;
        
            if ( self::checkXattrSupport() AND self::checkXattrFilesystemSupport($this->cache_folder) ) {

                $return = self::setXattr($shadowName, $shadowData, $shadowTtl);

            } else {

                $return = self::setGhost($shadowName, $shadowData, $shadowTtl);

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $return;

    }
    
    public function get($name) {

        if ( empty($name) ) {
            
            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Name of object cannot be empty");
            
            throw new CacheException("Name of object cannot be empty");
            
        }

        try {
            
            $shadowName = $this->cache_folder . md5($name)."-".$this->getScope();
        
            if ( self::checkXattrSupport() AND self::checkXattrFilesystemSupport($this->cache_folder) ) {

                $return = self::getXattr($shadowName, $this->getTime());

            } else {

                $return = self::getGhost($shadowName, $this->getTime());

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return is_null($return) ? $return : unserialize($return);

    }
    
    public function flush($name=null) {

        // flush entire scope
        if ( is_null($name) ) $filesList = glob($this->cache_folder."*-".$this->getScope().".{cache,expire}", GLOB_BRACE);

        else $filesList = glob($this->cache_folder.md5($name)."-".$this->getScope().".{cache,expire}", GLOB_BRACE);

        foreach ($filesList as $file) {

            if ( unlink($file) === false ) throw new CacheException("Failed to unlink cache file");

        }

        return true;

    }

    public function purge() {

        $filesList = glob($this->cache_folder."*.{cache,expire}", GLOB_BRACE);

        foreach ($filesList as $file) {

            if ( unlink($file) === false ) throw new CacheException("Failed to unlink cache file");

        }

        return true;

    }

    public function status($currentScope=false) {

        $currentScope = filter_var($currentScope, FILTER_VALIDATE_BOOLEAN);

        if ( $currentScope ) $filesList = glob($this->cache_folder."*-".$this->getScope().".cache");

        else $filesList = glob($this->cache_folder."*.cache");

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
            "online"    => true,
            "objects"   => count($filesList),
            "options"   => $options
        );

    }

    private static function setXattr($name, $data, $ttl) {

        $cacheFile = $name . ".cache";

        $return = true;

        $cached = file_put_contents($cacheFile, $data, LOCK_EX);

        if ( $cached === false ) {

            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error writing cache object, exiting gracefully");

            $return = false;

        }

        $tagged = xattr_set($cacheFile, "EXPIRE", $ttl, XATTR_DONTFOLLOW);

        if ( $tagged === false ) {

            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error writing cache ttl, exiting gracefully");

            $return = false;

        }

        return $return;

    }

    private static function setGhost($name, $data, $ttl) {

        $cacheFile = $name . ".cache";

        $cacheGhost = $name . ".expire";

        $cached = file_put_contents($cacheFile, $data, LOCK_EX);
        
        $return = true;

        if ( $cached === false ) {

            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error writing cache object, exiting gracefully");

            $return = false;

        }

        $tagged = file_put_contents($cacheGhost, $ttl, LOCK_EX);

        if ( $tagged === false ) {

            if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error writing cache ttl, exiting gracefully");

            $return = false;

        }

        return $return;

    }

    private static function getXattr($name, $time) {

        $cacheFile = $name . ".cache";

        if ( file_exists($cacheFile) ) {

            $expire = xattr_get($cacheFile, "EXPIRE", XATTR_DONTFOLLOW);

            if ( $expire === false ) {
                
                if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error reading cache ttl, exiting gracefully");
                
                $return = null;

            } else if ( $expire < $time ) {
                
                $return = null;
                
            } else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) {
                    
                    if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error reading cache content, exiting gracefully");
                    
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

    private static function getGhost($name, $time) {

        $cacheFile = $name . ".cache";

        $cacheGhost = $name . ".expire";

        if ( file_exists($cacheFile) ) {

            $expire = file_get_contents($cacheGhost);
            
            if ( $expire === false ) {
                
                if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error reading cache ttl, exiting gracefully");
                
                $return = null;

            } else if ( $expire < $time ) {
                
                $return = null;
                
            } else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) {
                    
                    if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Error reading cache content, exiting gracefully");
                    
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
    
    private static function checkCacheFolder($folder) {
        
        return is_writable( $folder );
        
    }
    
    private static function checkXattrSupport() {
        
        return function_exists("xattr_supported");
        
    }
    
    private static function checkXattrFilesystemSupport($folder) {
        
        return xattr_supported($folder);
        
    }
    
}