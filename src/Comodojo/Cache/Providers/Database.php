<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Providers\AbstractProvider;
use \Comodojo\Cache\Components\InstanceTrait;
use \Comodojo\Database\EnhancedDatabase;
use \Psr\Log\LoggerInterface;
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

class Database extends AbstractProvider {

    use InstanceTrait;

    /**
     * Database table
     *
     * @var string
     */
    private $table;

    /**
     * Prefix for table
     *
     * @var string
     */
    private $table_prefix;

    /**
     * Class constructor
     *
     * @param   EnhancedDatabase $dbh
     * @param   string           $table          Name of table
     * @param   string           $table_prefix   Prefix for table
     * @param   LoggerInterface  $logger         Logger instance
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct(EnhancedDatabase $dbh, $table, $table_prefix = null, LoggerInterface $logger = null) {

        if ( empty($table) ) throw new CacheException("Database table cannot be undefined");

        $this->table = $table;

        $this->table_prefix = empty($table_prefix) ? null : $table_prefix;

        parent::__construct($logger);

        $this->setInstance($dbh);

        $this->table = $table;

        $this->instance->autoClean();

    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $data, $ttl = null) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");

        if ( is_null($data) ) throw new CacheException("Object content cannot be null");

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {

            $namespace = $this->getNamespace();

            $this->setTtl($ttl);

            $expire = $this->getTime() + $this->ttl;

            $is_in_cache = self::getCacheObject($this->instance, $this->table, $this->table_prefix, $name, $namespace);

            if ( $is_in_cache->getLength() != 0 ) {

                self::updateCacheObject($this->instance, $this->table, $this->table_prefix, $name, serialize($data), $namespace, $expire);

            } else {

                self::addCacheObject($this->instance, $this->table, $this->table_prefix, $name, serialize($data), $namespace, $expire);

            }

        } catch (CacheException $ce) {

            throw $ce;

        } catch (DatabaseException $de) {

            $this->logger->error("Error writing cache object (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function get($name) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");

        if ( !$this->isEnabled() ) return null;

        $this->resetErrorState();

        try {

            $namespace = $this->getNamespace();

            $is_in_cache = self::getCacheObject($this->instance, $this->table, $this->table_prefix, $name, $namespace, $this->getTime());

            if ( $is_in_cache->getLength() != 0 ) {

                $value = $is_in_cache->getData();

                $return = unserialize($value[0]['data']);

            } else {

                $return = null;

            }

        } catch (CacheException $ce) {

            throw $ce;

        } catch (DatabaseException $de) {

            $this->logger->error("Error reading cache object (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));

            $this->setErrorState();

            $return = null;

        }

        return $return;

    }

    /**
     * {@inheritdoc}
     */
    public function delete($name = null) {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {

            $this->instance->tablePrefix($this->table_prefix)
                ->table($this->table)
                ->where("namespace", "=", $this->getNamespace());

            if ( !empty($name) ) $this->instance->andWhere("name", "=", $name);

            $this->instance->delete();

        } catch (DatabaseException $de) {

            $this->logger->error("Failed to delete cache (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {

            $this->instance->tablePrefix($this->table_prefix)->table($this->table)->truncate();

        } catch (DatabaseException $de) {

            $this->logger->error("Failed to flush cache (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function status() {

        $this->resetErrorState();

        try {

            $this->instance->tablePrefix($this->table_prefix)
                ->table($this->table)
                ->keys('COUNT::name=>count');

            $count = $this->instance->get();

            $objects = $count->getData();

        } catch (DatabaseException $de) {

            $this->logger->error("Failed to get cache status (Database), exiting gracefully", array(
                "ERRORNO"   =>  $de->getCode(),
                "ERROR"     =>  $de->getMessage()
            ));

            $this->setErrorState();

            return array(
                "provider"  => "database",
                "enabled"   => false,
                "objects"   => 0,
                "options"   => array()
            );

        }

        return array(
            "provider"  => "database",
            "enabled"   => $this->isEnabled(),
            "objects"   => intval($objects[0]['count']),
            "options"   => array(
                'host'  =>  $this->instance->getHost(),
                'port'  =>  $this->instance->getPort(),
                'name'  =>  $this->instance->getName(),
                'user'  =>  $this->instance->getUser(),
                'model' =>  $this->instance->getModel()
            )
        );

    }

    /**
     * Get object from database
     *
     * @return  \Comodojo\Database\QueryResult
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private static function getCacheObject($dbh, $table, $table_prefix, $name, $namespace, $expire = null) {

        try {

            $dbh->tablePrefix($table_prefix)
                ->table($table)
                ->keys('data')
                ->where("name", "=", $name)
                ->andWhere("namespace", "=", $namespace); ;

            if ( is_int($expire) ) {

                $dbh->andWhere("expire", ">", $expire);

            }

            $match = $dbh->get();


        } catch (DatabaseException $de) {

            throw $de;

        }

        return $match;

    }

    /**
     * Update a cache element
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private static function updateCacheObject($dbh, $table, $table_prefix, $name, $data, $scope, $expire) {

        try {

            $dbh->tablePrefix($table_prefix)
                ->table($table)
                ->keys(array('data', 'expire'))
                ->values(array($data, $expire))
                ->where("name", "=", $name)
                ->andWhere("namespace", "=", $scope)
                ->update();

        } catch (DatabaseException $de) {

            throw $de;

        }

    }

    /**
     * Add a cache element
     *
     * @throws  \Comodojo\Exception\DatabaseException
     */
    private static function addCacheObject($dbh, $table, $table_prefix, $name, $data, $scope, $expire) {

        try {

            $dbh->tablePrefix($table_prefix)
                ->table($table)
                ->keys(array('name', 'data', 'namespace', 'expire'))
                ->values(array($name, $data, $scope, $expire))
                ->store();

        } catch (DatabaseException $de) {

            throw $de;

        }

    }

    /**
     * Generate an EnhancedDatabase object
     *
     * @return \Comodojo\Database\EnhancedDatabase
     * @throws \Comodojo\Exception\CacheException
     */
    public static function getDatabase($model, $host, $port, $database, $user, $password = null) {

        try {

            $dbh = new \Comodojo\Database\EnhancedDatabase(
                $model,
                $host,
                $port,
                $database,
                $user,
                $password
            );

        } catch (Exception $e) {

            throw new CacheException("Cannot init database: (".$e->getCode().") ".$e->getMessage());

        }

        return $dbh;

    }

}
