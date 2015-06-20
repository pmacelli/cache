<?php namespace Comodojo\Cache\CacheObject;

use \Comodojo\Cache\CacheTrait\CacheTrait;
use \Comodojo\Exception\CacheException;

/**
 * Cache controller
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

class CacheObject {

    use CacheTrait;

    /**
     * Is cache enabled?
     *
     * @var int
     */
    protected $enabled = true;

    private $cache_id = null;

    private $error_state = false;

    /**
     * Class constructor
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct( $logger=null ) {

        try {
            
            $this->setTime();
            
            $this->setTtl();

            $this->cache_id = self::getUniqueId();
            
        } catch (CacheException $ce) {
            
            throw $ce;
            
        }

        if ( $logger instanceof \Monolog\Logger ) $this->setLogger($logger);
        
    }

    /**
     * Set current time
     *
     * @param    float    $time    Set current time (in msec - float)
     * 
     * @return \Comodojo\Cache\CacheObject\CacheObject
     * @throws \Comodojo\Exception\CacheException
     */
    final public function setTime( $time=null ) {
        
        if ( is_null($time) ) $this->current_time = time();

        else if ( preg_match('/^[0-9]{10}$/', $time) ) $this->current_time = $time;
        
        else {
            
            throw new CacheException("Invalid time");
            
        }

        return $this;
        
    }

    /**
     * Set time to live for cache
     *
     * @param    int    $ttl    Time to live (in secs)
     * 
     * @return \Comodojo\Cache\CacheObject\CacheObject
     * @throws \Comodojo\Exception\CacheException
     */
    final public function setTtl( $ttl=null ) {
        
        if ( is_null($ttl) ) {
            
            $this->ttl = defined('COMODOJO_CACHE_DEFAULT_TTL') ? COMODOJO_CACHE_DEFAULT_TTL : 3600;
            
        } else if ( is_int($ttl) ) {
            
            $this->ttl = $ttl;
            
        } else {

            throw new CacheException("Invalid time to live");
            
        }

        return $this;
        
    }

    /**
     * Set namespace for cache
     *
     * @param    string    $namespace    Selected namespace (64 chars limited)
     * 
     * @return \Comodojo\Cache\CacheObject\CacheObject
     * @throws \Comodojo\Exception\CacheException
     */
    final public function setNamespace( $namespace ) {

        if ( preg_match('/^[0-9a-zA-Z]+$/', $namespace) AND strlen($namespace) <= 64 ) {
            
            $this->namespace = strtoupper($namespace);
            
        } else {
            
            throw new CacheException("Invalid namespace");
            
        }

        return $this;

    }

    /**
     * Set the monolog instance
     *
     * @param    \Monolog\Logger    $logger
     * 
     * @return \Comodojo\Cache\CacheObject\CacheObject
     */
    final public function setLogger( \Monolog\Logger $logger ) {

        $this->logger = $logger;

        return $this;

    }

    final public function enable() {

        $this->enabled = true;

    }

    final public function disable() {

        $this->enabled = false;

    }

    final public function isEnabled() {

        return $this->enabled;

    }

    final public function getCacheId() {

        return $this->cache_id;

    }

    final public function setErrorState() {

        $this->error_state = true;

        return $this;

    }

    final public function resetErrorState() {

        $this->error_state = false;

        return $this;

    }

    final public function getErrorState() {

        return $this->error_state;

    }

    /**
     * Generate a unique id (64 chars)
     *
     * @return string
     */
    static protected function getUniqueId() {

        return substr(md5(uniqid(rand(), true)), 0, 64);

    }

}
