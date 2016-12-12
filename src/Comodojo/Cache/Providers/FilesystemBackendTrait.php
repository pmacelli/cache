<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * FileSystemBackendTrait
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

trait FilesystemBackendTrait {

    /**
     * Check if cache folder is writable
     *
     * @param   string  $folder
     *
     * @return  bool
     */
    public static function checkCacheFolder($folder) {

        return is_writable($folder);

    }

    /**
     * Check xattr (extension) support
     *
     * @return  bool
     */
    public static function checkXattrSupport() {

        return function_exists("xattr_supported");

    }

    /**
     * Check xattr (filesystem) support
     *
     * @return  bool
     */
    public static function checkXattrFilesystemSupport($folder) {

        return xattr_supported($folder);

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

            $message = "Error writing cache object $cacheFile, exiting gracefully";

            $this->logger->error($message);

            $this->setState(self::CACHE_ERROR, $message);

            return false;

        }

        $tagged = xattr_set($cacheFile, "EXPIRE", $ttl, XATTR_DONTFOLLOW);

        if ( $tagged === false ) {

            $message = "Error writing cache ttl $cacheFile (XATTR), exiting gracefully";

            $this->logger->error($message);

            $this->setState(self::CACHE_ERROR, $message);

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

            $message = "Error writing cache object $cacheFile, exiting gracefully";

            $this->logger->error($message);

            $this->setState(self::CACHE_ERROR, $message);

            return false;

        }

        $tagged = file_put_contents($cacheGhost, $ttl, LOCK_EX);

        if ( $tagged === false ) {

            $message = "Error writing cache ttl $cacheFile (GHOST), exiting gracefully";

            $this->logger->error($message);

            $this->setState(self::CACHE_ERROR, $message);

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
    protected function getXattr($name) {

        $cacheFile = $name.".cache";

        if ( $this->hasXattr($name) === true ) {

            $data = file_get_contents($cacheFile);

            if ( $data === false ) {

                $message = "Error reading cache content $cacheFile (XATTR), exiting gracefully";

                $this->logger->error($message);

                $this->setState(self::CACHE_ERROR, $message);

                return null;

            }

            return $data;

        }

        return null;

    }

    /**
     * Get a cache element using ghost file
     *
     * @param   string  $name    Name for cache element
     * @param   int     $time
     *
     * @return  mixed
     */
    protected function getGhost($name) {

        $cacheFile = $name.".cache";

        if ( $this->hasGhost($name) === true ) {

            $data = file_get_contents($cacheFile);

            if ( $data === false ) {

                $message = "Error reading cache content $cacheFile (XATTR), exiting gracefully";

                $this->logger->error($message);

                $this->setState(self::CACHE_ERROR, $message);

                return null;

            }

            return $data;

        }

        return null;

    }

    /**
     * Check if element exists
     *
     * @param   string  $name    Name for cache element
     *
     * @return  mixed
     */
    protected function hasXattr($name) {

        $time = time();

        $cacheFile = $name.".cache";

        if ( file_exists($cacheFile) ) {

            $expire = xattr_get($cacheFile, "EXPIRE", XATTR_DONTFOLLOW);

            if ( $expire === false ) {

                $message = "Error reading cache ttl $cacheFile (XATTR), exiting gracefully";

                $this->logger->error($message);

                $this->setState(self::CACHE_ERROR, $message);

                return false;

            } else if ( $expire < $time && $expire != 0 ) {

                return false;

            } else {

                return true;

            }

        }

        return false;

    }

    /**
     * Get a cache element using ghost file
     *
     * @param   string  $name    Name for cache element
     * @param   int     $time
     *
     * @return  mixed
     */
    protected function hasGhost($name) {

        $time = time();

        $cacheFile = $name.".cache";

        $cacheGhost = $name.".expire";

        if ( file_exists($cacheFile) && file_exists($cacheFile) ) {

            $expire = file_get_contents($cacheGhost);

            if ( $expire === false ) {

                $message = "Error reading cache ttl $cacheGhost (GHOST), exiting gracefully";

                $this->logger->error($message);

                $this->setErrorState(1, $message);

                return false;

            } else if ( intval($expire) < $time && $expire != 0 ) {

                return false;

            } else {

                return true;

            }

        }

        return false;

    }

}
