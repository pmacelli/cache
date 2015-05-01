<?php namespace Comodojo\Cache\CacheObject;

use \Comodojo\Exception\CacheException;

/**
 * Cache controller
 * 
 * @package     Comodojo dispatcher
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

class CacheObject {

    private $fail_silently = null;
    
    private $scope = "GLOBAL";

    private $current_time = null;
    
    protected $logger = null;
    
    protected $ttl = null;

    public function __construct( $fail_silently=false ) {

        $this->setTime();
        
        $this->ttl = defined('COMODOJO_CACHE_DEFAULT_TTL') ? COMODOJO_CACHE_DEFAULT_TTL : 3600;

        $this->fail_silently = defined('COMODOJO_CACHE_FAIL_SILENTLY') ? filter_var(COMODOJO_CACHE_FAIL_SILENTLY, FILTER_VALIDATE_BOOLEAN) : filter_var($fail_silently, FILTER_VALIDATE_BOOLEAN);

    }

    public function setScope($scope) {

        if ( preg_match('/^[0-9a-zA-Z]+$/', $scope) ) $this->scope = strtoupper($scope);
        
        else {
            
            if ( $logger instanceof \Monolog\Logger ) $this->logger->addError("Invalid cache scope");
            
            if ( $this->should_fail_silently() === false ) throw new CacheException("Invalid cache scope");
            
        }
        
        return $this;

    }

    public function getScope() {

        return $this->scope;

    }

    final public function setLogger(\Monolog\Logger $logger) {

        $this->logger = $logger;
        
        return $this;

    }
    
    final public function getLogger() {
        
        return $this->logger;
        
    }
    
    final public function setTime($time=null) {
        
        if ( is_null($time) ) $this->current_time = microtime(true);
        
        else if ( preg_match('/^[0-9]{10}.[0-9]{4}$/', $time) ) $this->current_time = $time;
        
        else {
            
            if ( $logger instanceof \Monolog\Logger ) $this->logger->addError("Invalid time");
            
            if ( $this->should_fail_silently() === false ) throw new CacheException("Invalid time");
            
        }

        return $this;
        
    }
    
    final public function getTime() {
        
        return $this->current_time;
        
    }
    
    final public function should_fail_silently() {
        
        return $this->fail_silently;
        
    }

}
