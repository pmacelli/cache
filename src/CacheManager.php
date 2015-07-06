<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Comodojo\Exception\CacheException;

/**
 * Cache manager
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

class CacheManager {

    const PICK_FIRST   = 1;
    const PICK_LAST    = 2;
    const PICK_RANDOM  = 3;
    const PICK_BYWEIGHT= 4;
    const PICK_ALL     = 5;

    private $caches = array();

    private $cache_weights = array();

    private $selector = null;

    private $selected_cache = null;

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

    public function __construct( $select_mode=null, \Monolog\Logger $logger=null ) {

        $this->selector = filter_var($select_mode, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1, 
                'max_range' => 4,
                'default'   => 3
            )
        ));

        try {
            
            $this->setTime();
            
            $this->setTtl();

            if ( !is_null($logger) ) $this->setLogger($logger);
            
        } catch (CacheException $ce) {
            
            throw $ce;
            
        }

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

    public function raiseError($message, $parameters=array()) {

        if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError($message, $parameters);

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

        foreach ($this->caches as $cache) $cache->setTime($time);

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

        foreach ($this->caches as $cache) $cache->setTtl($ttl);

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

        foreach ($this->caches as $cache) $cache->setNamespace($namespace);

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

        foreach ($this->caches as $cache) $cache->setLogger($logger);

        return $this;

    }

    public function addProvider( CacheInterface $cache_provider, $weight=0 ) {

        $corrected_weight = filter_var($weight, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 0, 
                'max_range' => 100,
                'default'   => 0
            )
        ));

        $cache_id = $cache_provider->getCacheId();

        if ( array_key_exists($cache_id, $this->caches) ) throw new CacheException("Cache provider already registered");

        $cache_provider->setTime( $this->getTime() )->setTtl( $this->getTtl() );

        if ( $this->logger instanceof \Monolog\Logger ) $cache_provider->setLogger($this->logger);

        $this->caches[$cache_id] = $cache_provider;

        $this->cache_weights[$cache_id] = $weight;

        return $cache_id;

    }

    public function removeProvider($cache_id) {

        if ( array_key_exists($cache_id, $this->caches) AND array_key_exists($cache_id, $this->cache_weights) ) {

            unset($this->caches[$cache_id]);

            unset($this->cache_weights[$cache_id]);

        } else {

            throw new CacheException("Cache not registered");

        }

        return true;

    }

    public function getProviders($type=null) {

        $providers = array();
        
        if ( is_null($type) ) {
        
            foreach ( $this->caches as $id => $cache ) $providers[$id] = get_class($cache); 
            
        } else {
            
            foreach ( $this->caches as $id => $cache ) {
                
                $provider_class = get_class($cache);
                
                if ( $provider_class == $type ) $providers[$id] = get_class($cache); 

            }
            
        }
        
        return $providers;
        
    }

    public function set($name, $data, $ttl=null) {

        $set = array();

        try {
        
            foreach ($this->caches as $cache_id => $cache) {

                $set[$cache_id] = $cache->set($name, $data, $ttl);

            }

        } catch (CacheException $ce) {

            throw $ce;
            
        }

        return $set;

    }

    public function get($name) {

        reset($this->caches);
        
        $this->selected_cache = null;

        $result = null;

        try {
        
            switch ( $this->selector ) {
            
                case 1:

                    $result = $this->getCacheByLoop( $this->caches, $name );

                    break;

                case 2:

                    $result = $this->getCacheByLoop( array_reverse($this->caches, true), $name );
                    
                    break;

                case 3:

                    $result = $this->getRandomCache( $this->caches, $name );
                    
                    break;

                case 4:

                    $result = $this->getCacheByWeight( $this->caches, $this->cache_weights, $name );

                    break;

                case 5:

                    $values = array();

                    foreach ($this->caches as $cache) {
                        
                        $values[] = $cache->get($name);

                    }

                    if ( count(array_unique($values)) === 1 ) {

                        $result = $values[0];
                        
                    } else {

                        $this->raiseError("Inconsistent values in cache providers, exiting gracefully");

                        $result = null;

                    } 

                    break;
                
            }

        } catch (\Exception $e) {
            
            throw $ce;

        }

        return $result;

    }

    public function delete($name=null) {

        $delete = array();

        try {

            foreach ($this->caches as $cache_id => $cache) {

                $delete[$cache_id] = $cache->delete($name);

            }
            
        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $delete;

    }

    public function flush() {

        $flush = array();

        try {

            foreach ($this->caches as $cache_id => $cache) {

                $flush[$cache_id] = $cache->flush();

            }
            
        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $flush;

    }

    public function status() {

        $status = array();

        try {

            foreach ($this->caches as $cache_id => $cache) {

                $status[$cache_id] = $cache->status();

            }
            
        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $status;

    }

    public function getSelectedCache() {

        return $this->selected_cache;

    }

    private function getCacheByLoop($caches, $name) {

        $result = null;

        $active_cache = false;

        foreach ($caches as $cache) {
            
            if ( $cache->isEnabled() ) {

                $result = $cache->get($name);

                if ( $cache->getErrorState() === false ) {

                    $this->selected_cache = $cache->getCacheId();

                    $active_cache = true;

                    break;

                }

            }

        }

        if ( $active_cache === false ) $this->raiseError("Cannot find an active cache provider (Manager), exiting gracefully");

        return $result;

    }

    private function getRandomCache($caches, $name) {

        $result = null;

        $active_cache = false;

        for ($i=0; $i < sizeof($caches); $i++) { 
            
            $cache_id = array_rand($caches);

            $cache = $caches[$cache_id];

            if ( $cache->isEnabled() ) {

                $result = $cache->get($name);

                if ( $cache->getErrorState() === false ) {

                    $this->selected_cache = $cache_id;

                    $active_cache = true;

                    break;

                } else {

                    unset($caches[$cache_id]);

                }

            } else {

                unset($caches[$cache_id]);

            }

        }

        if ( $active_cache === false ) $this->raiseError("Cannot find an active cache provider (Manager), exiting gracefully");

        return $result;

    }

    private function getCacheByWeight($caches, $weights, $name) {

        $result = null;

        $active_cache = false;

        for ($i=0; $i < sizeof($weights); $i++) { 
            
            $cache_ids = array_keys($weights, max($weights));

            $cache_id = $cache_ids[0];

            $cache = $caches[$cache_id];

            if ( $cache->isEnabled() ) {

                $result = $cache->get($name);

                if ( $cache->getErrorState() === false ) {

                    $this->selected_cache = $cache_id;

                    $active_cache = true;

                    break;

                } else {

                    unset($weights[$cache_id]);

                }

            } else {

                unset($weights[$cache_id]);

            }

        }

        if ( $active_cache === false ) $this->raiseError("Cannot find an active cache provider (Manager), exiting gracefully");

        return $result;

    }

} 
 