<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Comodojo\Database\EnhancedDatabase;
use \Comodojo\Exception\DatabaseException;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * Database cache class
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

class DatabaseCache extends CacheObject implements CacheInterface {

    private $dbh = null;
    
    private $table = null;
    
    private $table_prefix = null;

    public function __construct( EnhancedDatabase $dbh, $table=null, $table_prefix=null ) {
    
        if ( !empty($table) ) {
            
            $this->table = $table;
            
        } else if ( defined("COMODOJO_CACHE_DATABASE_TABLE") ) {
            
            $this->table = COMODOJO_CACHE_DATABASE_TABLE;
            
        } else {
            
            throw new CacheException("Database table cannot be undefined");
            
        }
        
        if ( empty($table_prefix) ) {
            
            $this->table_prefix = defined("COMODOJO_CACHE_DATABASE_TABLEPREFIX") ? COMODOJO_CACHE_DATABASE_TABLEPREFIX : null;
            
        } else {
            
            $this->table_prefix = $table_prefix;
            
        }
    
        $this->dbh = $dbh;
        
        $this->dbh->autoClean();
        
        try {
            
            parent::__construct();
            
        }
        
        catch ( CacheException $ce ) {
            
            throw $ce;
            
        }
       
    }

    public function set($name, $data, $ttl=null) {
        
        if ( empty($name) ) {
            
            throw new CacheException("Name of object cannot be empty");
            
        }
        
        if ( is_null($data) ) {
            
            throw new CacheException("Object content cannot be null");
            
        }

        if ( !$this->isEnabled() ) return false;

        try {

            $namespace = $this->getNamespace();
            
            $this->setTtl($ttl);
            
            $expire = $this->getTime() + $this->ttl;
            
            $is_in_cache = self::getCacheObject($this->dbh, $name, $namespace);
            
            if ( $is_in_cache->getLength() != 0 ) {
                
                self::updateCacheObject($this->dbh, $name, serialize($data), $namespace, $expire );
                
            } else {
                
                self::addCacheObject($this->dbh, $name, serialize($data), $namespace, $expire );
                
            }

        } catch (CacheException $ce) {
            
            throw $ce;

        } catch (DatabaseException $de) {
            
            $this->raiseError("Error writing cache object (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));
            
            return false;
            
        }
        
        return true;
        
    }
    
    public function get($name) {
        
        if ( empty($name) ) {
            
            throw new CacheException("Name of object cannot be empty");
            
        }

        if ( !$this->isEnabled() ) return null;
        
        try {

            $namespace = $this->getNamespace();
            
            $is_in_cache = self::getCacheObject($this->dbh, $name, $namespace, $this->getTime());
            
            if ( $is_in_cache->getLength() != 0 ) {
            
                $value = $is_in_cache->getData();
                
                $return = unserialize($value[0][$name]);
               
            } else {
                
                $return = null;
                
            }

        } catch (CacheException $ce) {
            
            throw $ce;

        } catch (DatabaseException $de) {
           
            $this->raiseError("Error reading cache object (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));
            
            $return = null;
            
        }
        
        return $return;
        
    }
    
    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;
        
        try {
            
            $this->dbh->tablePrefix($this->table_prefix)
                ->table($this->table)
                ->where("namespace","=",$this->getNamespace());
                
            if ( !empty($name) ) {
                
                $this->dbh->andWhere("name","=",$name);
                
            }
            
            $this->dbh->delete();

        } catch (DatabaseException $de) {
           
            $this->raiseError("Failed to delete cache (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));
            
            return false;

        }
        
        return true;
        
    }
    
    public function flush() {
        
        if ( !$this->isEnabled() ) return false;

        try {
            
            $this->dbh->tablePrefix($this->table_prefix)->table($this->table)->truncate();

        } catch (DatabaseException $de) {
           
            $this->raiseError("Failed to flush cache (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));
            
            return false;
            
        }
        
        return true;
        
    }
    
    public function status() {
        
        try {
            
            $this->dbh->tablePrefix($this->table_prefix)
                ->table($this->table)
                ->keys('COUNT::id=>count');
            
            $count = $this->dbh->get();

        } catch (DatabaseException $de) {
           
            throw $de;
            
        }
        
        return array(
            "provider"  => "database",
            "enabled"   => $this->isEnabled(),
            "objects"   => $count[0]['count'],
            "options"   => array(
                "model" => $this->dbh->model
            )
        );
        
    }

    public final function getInstance() {

        return $this->dbh;

    }
    
    static private function getCacheObject($dbh, $table, $table_prefix, $name, $scope, $expire=null) {
        
        try {
            
            $dbh->tablePrefix($table_prefix)
                ->table($table)
                ->keys('data')
                ->where("name","=",$name)
                ->andWhere("namespace","=",$scope); ;
                
            if ( is_int($expire) ) {
                
                $dbh->andWhere("expire",">",$expire); 
                
            }
            
            $match = $dbh->get();
            
            
        } catch (DatabaseException $de) {
            
            throw $de;
            
        }
        
        return $match;
        
    }
    
    static private function updateCacheObject($dbh, $table, $table_prefix, $name, $data, $scope, $expire) {
        
        try {
            
            $update = $dbh->tablePrefix($table_prefix)
                ->table($table)
                ->keys(array('data','expire'))
                ->values(array($data,$expire))
                ->where("name","=",$name)
                ->andWhere("namespace","=",$scope)
                ->update();
        
        } catch (DatabaseException $de) {
            
            throw $de;
            
        }
        
    }
    
    static private function addCacheObject($dbh, $table, $table_prefix, $name, $data, $scope, $expire) {
        
        try {
            
            $update = $dbh->tablePrefix($table_prefix)
                ->table($table)
                ->keys(array('name', 'data', 'namespace', 'expire'))
                ->values(array($name, $data, $scope, $expire))
                ->store();
        
        } catch (DatabaseException $de) {
            
            throw $de;
            
        }
        
    }
    
    static public function getDatabase($model=null, $host=null, $port=null, $database=null, $user=null, $password=null) {
        
        $model = is_null($model) ? ( defined("COMODOJO_CACHE_DATABASE_MODEL") ? COMODOJO_CACHE_DATABASE_MODEL : null ) : $model;
        $host = is_null($host) ? ( defined("COMODOJO_CACHE_DATABASE_MODEL") ? COMODOJO_CACHE_DATABASE_MODEL : null ) : $host;
        $port = is_null($port) ? ( defined("COMODOJO_CACHE_DATABASE_PORT") ? COMODOJO_CACHE_DATABASE_MODEL : null ) : $port;
        $database = is_null($database) ? ( defined("COMODOJO_CACHE_DATABASE_DATABASE") ? COMODOJO_CACHE_DATABASE_MODEL : null ) : $database;
        $user = is_null($user) ? ( defined("COMODOJO_CACHE_DATABASE_USER") ? COMODOJO_CACHE_DATABASE_MODEL : null ) : $user;
        $password = is_null($password) ? ( defined("COMODOJO_CACHE_DATABASE_PASSWORD") ? COMODOJO_CACHE_DATABASE_MODEL : null ) : $password;
        
        try {
            
            $dbh = new \Comodojo\Database\EnhancedDatabase(
                $model,
                $host,
                $port,
                $database,
                $user,
                $password
            );
            
        } catch (DatabaseException $de) {
            
            throw $de;
            
        } catch (Exception $e) {
            
            throw $e;
            
        }
        
        return $dbh;
        
    }
    
}