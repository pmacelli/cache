<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Components\AbstractProvider;
use \Comodojo\Cache\Components\FileSystemTools;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

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

abstract class AbstractFileSystemProvider extends AbstractProvider {
 
    /**
     * Current cache folder
     *
     * @var string
     */
    private $cache_folder;
 
    /**
     * Class constructor
     *
     * @throws CacheException
     */
    public function __construct($cache_folder, LoggerInterface $logger = null) {
        
        if ( empty($cache_folder) || !is_string($cache_folder) ) {
            
            throw new CacheException("Invalid or unspecified cache folder");
            
        }
        
        parent::__construct($logger);

        $this->cache_folder = $cache_folder[strlen($cache_folder) - 1] == "/" ? $cache_folder : ($cache_folder."/");
            
        if ( FileSystemTools::checkCacheFolder($this->cache_folder) === false ) {

            $this->logger->error("Cache folder $cache_folder is not writeable, disabling cache administratively");

            $this->disable();

        }

    }
    
    /**
     * Set a cache element using xattr
     *
     * @param   string  $name    Name for cache element
     * @param   mixed   $data    Data to cache
     * @param   int     $ttl     Time to live
     *
     * @return  bool
     */
    protected function setXattr($name, $data, $ttl) {

        $cacheFile = $name.".cache";

        $cached = file_put_contents($cacheFile, $data, LOCK_EX);

        if ( $cached === false ) {

            $this->logger->error("Error writing cache object $cacheFile, exiting gracefully", pathinfo($cacheFile));

            $this->setErrorState();

            return false;

        }

        $tagged = xattr_set($cacheFile, "EXPIRE", $ttl, XATTR_DONTFOLLOW);

        if ( $tagged === false ) {

            $this->logger->error("Error writing cache ttl cacheFile (XATTR), exiting gracefully", pathinfo($cacheFile));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    /**
     * Set a cache element using ghost file
     *
     * @param   string  $name    Name for cache element
     * @param   mixed   $data    Data to cache
     * @param   int     $ttl     Time to live
     *
     * @return  bool
     */
    protected function setGhost($name, $data, $ttl) {

        $cacheFile = $name.".cache";

        $cacheGhost = $name.".expire";

        $cached = file_put_contents($cacheFile, $data, LOCK_EX);
        
        if ( $cached === false ) {

            $this->logger->error("Error writing cache object $cacheFile, exiting gracefully", pathinfo($cacheFile));

            $this->setErrorState();

            return false;

        }

        $tagged = file_put_contents($cacheGhost, $ttl, LOCK_EX);

        if ( $tagged === false ) {

            $this->logger->error("Error writing cache ttl cacheGhost (GHOST), exiting gracefully", pathinfo($cacheGhost));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    /**
     * Get a cache element using xattr
     *
     * @param   string  $name    Name for cache element
     * @param   int     $time
     *
     * @return  mixed
     */
    protected function getXattr($name, $time) {

        $cacheFile = $name.".cache";

        if ( file_exists($cacheFile) ) {

            $expire = xattr_get($cacheFile, "EXPIRE", XATTR_DONTFOLLOW);

            if ( $expire === false ) {
                
                $this->logger->error("Error reading cache ttl $cacheFile (XATTR), exiting gracefully", pathinfo($cacheFile));

                $this->setErrorState();

                $return = null;

            } else if ( $expire < $time ) {
                
                $return = null;
                
            } else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) {
                    
                    $this->logger->error("Error reading cache content $cacheFile, exiting gracefully", pathinfo($cacheFile));

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

    /**
     * Get a cache element using ghost file
     *
     * @param   string  $name    Name for cache element
     * @param   int     $time
     *
     * @return  mixed
     */
    protected function getGhost($name, $time) {

        $cacheFile = $name.".cache";

        $cacheGhost = $name.".expire";

        if ( file_exists($cacheFile) ) {

            $expire = file_get_contents($cacheGhost);

            if ( $expire === false ) {

                $this->logger->error("Error reading cache ttl $cacheGhost (GHOST), exiting gracefully", pathinfo($cacheGhost));
                
                $return = null;

            } else if ( intval($expire) < $time ) {
                
                $return = null;
                
            } else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) {
                    
                    $this->logger->error("Error reading cache content $cacheFile, exiting gracefully", pathinfo($cacheFile));
                    
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
    
}
