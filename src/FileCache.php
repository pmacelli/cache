<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
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
	
	public function set() {}
	
	public function get() {}
	
	public function status() {}
	
	private static function checkCacheFolder($folder) {
	    
        return is_writable( dirname( $folder . '.placeholder' ) );
	    
	}
	
	private static function checkXattrSupport() {
	    
	    return function_exists("xattr_supported");
	    
	}
	
	private static function checkXattrFilesystemSupport($folder) {
	    
	    return xattr_supported($folder . '.placeholder');
	    
	}
    
}