<?php namespace Comodojo\Cache\CacheObject;

use \Comodojo\Cache\CacheInterface\CacheInterface;
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
 
abstract class CacheObject implements CacheInterface {

    /**
     * Is cache enabled?
     *
     * @var int
     */
    protected $enabled = true;

    /**
     * Current cache id
     *
     * @var string
     */
    private $cache_id = null;

    /**
     * Current error state
     *
     * @var bool
     */
    private $error_state = false;

    /**
     * Determine the current cache scope (default: GLOBAL)
     *
     * @var string
     */
    protected $namespace = "GLOBAL";

    /**
     * current time (in msec)
     *
     * @var float
     */
    protected $current_time = null;
    
    /**
     * Current instance of \Monolog\Logger
     *
     * @var \Monolog\Logger
     */
    protected $logger = null;
    
    /**
     * Cache ttl
     *
     * @var int
     */
    protected $ttl = null;

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
     * Raise an error using logger (monolog)
     *
     * @param    string    $message       The message to log
     * @param    array     $parameters    (optional) Additional parameters
     */
    public function raiseError($message, $parameters=array()) {

        if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError($message, $parameters);
    
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
     */
    abstract public function set($name, $data, $ttl=null);
    
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
     */
    abstract public function get($name);
    
    /**
     * Delete cache object (or entire namespace if $name is null)
     *
     * This method will throw only logical exceptions.
     * In case of failures, it will return a boolean false.
     *
     * @param   string  $name    Name for cache element
     *
     * @return  bool
     */
    abstract public function delete($name=null);
    
    /**
     * Clean cache objects in all namespaces
     *
     * This method will throw only logical exceptions.
     *
     * @return  bool
     */
    abstract public function flush();
    
    /**
     * Get cache status
     *
     * @return  array
     */
    abstract public function status();

    /**
     * Get current time
     *
     * @return float
     */
    final public function getTime() {
        
        return $this->current_time;
        
    }

    /**
     * Get current ttl
     *
     * @return int
     */
    final public function getTtl() {
        
        return $this->ttl;
        
    }

    /**
     * Get current namespace
     *
     * @return int
     */
    final public function getNamespace() {

        return $this->namespace;

    }

    /**
     * Get current logger
     *
     * @return \Monolog\Logger
     */
    final public function getLogger() {
        
        return $this->logger;
        
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

    /**
     * administratively enable cache
     *
     */
    final public function enable() {

        $this->enabled = true;

    }

    /**
     * administratively disable cache
     *
     */
    final public function disable() {

        $this->enabled = false;

    }

    /**
     * check if cache is enabled
     *
     * @return bool
     */
    final public function isEnabled() {

        return $this->enabled;

    }

    /**
     * return the id of the current cache provider
     *
     * @return string
     */
    final public function getCacheId() {

        return $this->cache_id;

    }

    /**
     * Put provider in error state
     *
     */
    final public function setErrorState() {

        $this->error_state = true;

        return $this;

    }

    /**
     * Reset error state
     *
     */
    final public function resetErrorState() {

        $this->error_state = false;

        return $this;

    }

    /**
     * Check if provider is in error state
     *
     */
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
