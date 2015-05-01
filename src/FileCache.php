<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Comodojo\Exception\CacheException;

/**
 * Cache controller
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

class FileCache extends CacheObject implements CacheInterface {
 
    private $cache_folder = null;
 
    public function __construct( $cache_folder = false, $fail_silently=false ) {

        if ( $cache_folder !== false ) $this->cache_folder = $cache_folder[strlen($cache_folder)-1] == "/" ? $cache_folder : ( $cache_folder . "/" );
        
        else if ( defined(COMODOJO_CACHE_FOLDER) ) $this->cache_folder = COMODOJO_CACHE_FOLDER[strlen(COMODOJO_CACHE_FOLDER)-1] == "/" ? COMODOJO_CACHE_FOLDER : ( COMODOJO_CACHE_FOLDER . "/" );
        
        else throw new CacheException("Invalid or unspecified cache folder");
        
        try {
            
            $location_available = self::checkCacheFolder($this->cache_folder);
            
        }
        
        catch ( CacheException $ce ) {
            
            throw $ce;
            
        }
        
        parent::__construct($fail_silently);

    }
    
    public function set($name, $data, $ttl=null) {

        if ( empty($name) ) throw new CacheException("Object name cannot be empty");

        else $shadowName = $this->cache_folder . md5($name)."-".$this->getScope();

        $shadowData = serialize($data);

        if ( is_int($ttl) AND $ttl > 1 ) $this->ttl = $ttl;

        $shadowTtl = $this->getTime() + $this->ttl;

        try {
        
            if ( self::checkXattrSupport() AND self::checkXattrFilesystemSupport($this->cache_folder) ) {

                $toReturn = self::setXattr($shadowName, $shadowData, $shadowTtl);

            } else {

                $toReturn = self::setGhost($shadowName, $shadowData, $shadowTtl);

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $toReturn;

    }
    
    public function get($name) {

        if ( empty($name) ) throw new CacheException("Object name cannot be empty");

        else $shadowName = $this->cache_folder . md5($name)."-".$this->getScope();

        try {
        
            if ( self::checkXattrSupport() AND self::checkXattrFilesystemSupport($this->cache_folder) ) {

                $toReturn = self::getXattr($shadowName, $this->getTime());

            } else {

                $toReturn = self::getGhost($shadowName, $this->getTime());

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $toReturn === false ? $toReturn : unserialize($toReturn);

    }
    
    public function flush($name=null) {}

    public function status() {}

    private static function setXattr($name, $data, $ttl) {

        $cacheFile = $name . ".cache";

        $cached = file_put_contents($cacheFile, $data);

        if ( $cached === false ) {

            throw new CacheException("Error writing cache file");

            // $this->logger->error('Error writing to cache', array(
            //     'CACHEFILE' => $cacheFile
            // ));
            
            // if ($this->fail_silently) {
            //     return false;
            // }
            // else {
            //     throw new IOException("Error writing to cache");
            // }

        }

        $tagged = xattr_set($cacheFile, "EXPIRE", $ttl, XATTR_DONTFOLLOW);

        if ( $tagged === false ) {

            throw new CacheException("Error writing cache ttl");

        }

        return true;

    }

    private static function setGhost($name, $data, $ttl) {

        $cacheFile = $name . ".cache";

        $cacheGhost = $name . ".expire";

        $cached = file_put_contents($cacheFile, $data, LOCK_EX);

        if ( $cached === false ) {

            throw new CacheException("Error writing cache file");

            // $this->logger->error('Error writing to cache', array(
            //     'CACHEFILE' => $cacheFile
            // ));
            
            // if ($this->fail_silently) {
            //     return false;
            // }
            // else {
            //     throw new IOException("Error writing to cache");
            // }

        }

        $tagged = file_put_contents($cacheGhost, $ttl, LOCK_EX);

        if ( $tagged === false ) {

            throw new CacheException("Error writing cache ttl");

        }

        return true;

    }

    private static function getXattr($name, $time) {

        $cacheFile = $name . ".cache";

        if ( file_exists($cacheFile) ) {

            $expire = xattr_get($cacheFile, "EXPIRE", XATTR_DONTFOLLOW);

            if ( $expire === false ) throw new CacheException("Error reading cache ttl");

            if ( $expire < $time ) $data = false;

            else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) throw new CacheException("Error reading cache");
                
            }

            return $data;

        }

        else return false;

    }

    private static function getGhost($name, $time) {

        $cacheFile = $name . ".cache";

        $cacheGhost = $name . ".expire";

        if ( file_exists($cacheFile) ) {

            $expire = file_get_contents($cacheGhost);

            if ( $expire === false ) throw new CacheException("Error reading cache ttl");

            if ( $expire < $time ) $data = false;

            else {

                $data = file_get_contents($cacheFile);

                if ( $data === false ) throw new CacheException("Error reading cache");
                
            }

            return $data;

        }

        else return false;

    }
    
    private static function checkCacheFolder($folder) {
        
        return is_writable( dirname( $folder . '.placeholder' ) );
        
    }
    
    private static function checkXattrSupport() {
        
        return function_exists("xattr_supported");
        
    }
    
    private static function checkXattrFilesystemSupport($folder) {
        
        return xattr_supported($folder);// . '.placeholder');
        
    }
    
}