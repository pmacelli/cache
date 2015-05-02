<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Comodojo\Exception\CacheException;

if ( !defined("COMODOJO_CACHE_PICK_FIRST") ) define("COMODOJO_CACHE_PICK_FIRST", 1);
if ( !defined("COMODOJO_CACHE_PICK_LAST") ) define("COMODOJO_CACHE_PICK_LAST", 2);
if ( !defined("COMODOJO_CACHE_PICK_RANDOM") ) define("COMODOJO_CACHE_PICK_RANDOM", 3);
if ( !defined("COMODOJO_CACHE_PICK_ALL") ) define("COMODOJO_CACHE_PICK_ALL", 4);

/**
 * Cache manager
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

class CacheManager extends CacheObject implements CacheInterface {

    private $caches = array();

    private $selector = null;

    public function __construct(integer $select_mode=null, $fail_silently=false) {

        $this->selector = filter_var($select_mode, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1, 
                'max_range' => 4,
                'default'   => 1
            )
        ));

        parent::__construct($fail_silently);

    }

    public function add($name, CacheInterface $cache) {

        if ( !preg_match('/^[0-9a-zA-Z]+$/', $name) ) throw new CacheException("Invalid name for a cache instance");
        
        if ( array_key_exists($name, $this->caches) ) throw new CacheException("Cache name already registered");

        $cache->setTime($this->getTime);

        if ( $this->logger instanceof \Monolog\Logger ) $cache->setLogger($this->logger);

        $this->caches[$name] = $cache;

        return $this;

    }

    public function remove($name) {

        if ( !array_key_exists($name, $this->caches) ) throw new CacheException("Cache not registered");

        unset($this->caches[$name]);

        return $this;

    }

    public function set($name, $data, $ttl=null) {

        try {
        
            foreach ($this->caches as $cache) {
            
                $cache->setScope($this->getScope)->set($name, $data, $ttl=null);

            }

        } catch (CacheException $ce) {

            throw $ce;
            
        }

        return true;

    }

    public function get($name) {

        reset($this->caches);

        try {
        
            switch ( $this->selector ) {
            
                case 1:

                    $cache = current($this->caches);

                    $result = $cache->setScope($this->getScope)->get($name);

                    break;

                case 2:

                    $cache = end($this->caches);

                    $result = $cache->setScope($this->getScope)->get($name);
                    
                    break;

                case 3:

                    $cache = array_rand($this->caches);

                    $result = $cache->setScope($this->getScope)->get($name);
                    
                    break;

                case 4:

                    foreach ($caches as $cache) {
                        
                        $result = $cache->setScope($this->getScope)->get($name);

                        if ( $result !== false) break;

                    }

                    break;              
                
            }

        } catch (Exception $e) {
            
            throw $ce;

        }

        return $result;

    }

    public function flush($name=null) {

        try {

            foreach ($this->caches as $cache) $cache->setScope($this->getScope)->flush($name);
            
        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return true;

    }

    public function purge() {

        try {

            foreach ($this->caches as $cache) $cache->setScope($this->getScope)->purge();
            
        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return true;

    }

    public function status() {

        $status = array();

        foreach ($this->caches as $cache) array_push($status, $cache->status());

        return $status;

    }

} 
 