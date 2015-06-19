<?php namespace Comodojo\Cache\CacheObject;

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

    protected $enabled = true;

    /**
     * Class constructor
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct() {

        try {
            
            $this->setTime();
            
            $this->setTtl();
            
        } catch (CacheException $ce) {
            
            throw $ce;
            
        }
        
    }

    public function raiseError($message, $parameters=array()) {

        if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError($message, $parameters);

        var_export($message);
        
        var_export($parameters);

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

    /**
     * Set current time
     *
     * @param    float    $time    Set current time (in msec - float)
     * 
     * @return \Comodojo\Cache\CacheObject\CacheObject
     * @throws \Comodojo\Exception\CacheException
     */
    final public function setTime( $time=null ) {
        
        if ( is_null($time) ) $this->current_time = microtime(true);
        
        else if ( preg_match('/^[0-9]{10}.[0-9]{4}$/', $time) ) $this->current_time = $time;
        
        else {
            
            // $this->raiseError("Invalid time");

            throw new CacheException("Invalid time");
            
        }

        return $this;
        
    }
    
    /**
     * Get current time
     *
     * @return float
     */
    final public function getTime() {
        
        return $this->current_time;
        
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

            // $this->raiseError("Invalid time to live");
            
            throw new CacheException("Invalid time to live");
            
        }
        
        return $this;
        
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
            
            // $this->raiseError("Invalid namespace");

            throw new CacheException("Invalid namespace");
            
        }
        
        return $this;

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
     * Get current logger
     *
     * @return \Monolog\Logger
     */
    final public function getLogger() {
        
        return $this->logger;
        
    }

}
