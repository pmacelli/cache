<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Redis;
use \Comodojo\Exception\CacheException;
use \RedisException;
use \Exception;

/**
 * Redis cache class using PhpRedis extension
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

class PhpRedisCache extends CacheObject implements CacheInterface {

    private $instance = null;

    public function __construct( $server, $port=6379, $timeout=0 ) {

        if ( empty($server) ) {

            throw new CacheException("Invalid or unspecified memcached server");

        }

        $this->instance = new Redis();

        $port = filter_var($port, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 1,
                "max_range" => 65535,
                "default" => 6379 )
            )
        );

        $weight = filter_var($timeout, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 0,
                "default" => 0 )
            )
        );

        if ( $this->instance->connect($server, $port=6379, $timeout=0) === false ) {

            $this->raiseError( "Error communicating with server", array( $this->instance->getLastError() ) );

            $this->disable();

        } else {

            try {
            
                parent::__construct();
                
            }
            
            catch ( CacheException $ce ) {
                
                throw $ce;
                
            }

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

        $this->resetErrorState();

        try {
            
            $this->setTtl($ttl);

            $namespace = $this->getNamespaceKey();

            if ( $namespace === false ) $namespace = $this->setNamespaceKey();

            if ( $namespace === false ) {

                $this->raiseError( "Error writing cache (PhpRedis), exiting gracefully", array( $this->instance->getLastError() ) );

                $this->setErrorState();

                $return = false;

            } else {

                $shadowName = $namespace."-".md5($name);
            
                $shadowTtl = $this->ttl;

                $shadowData = serialize($data);
                
                $return = $this->instance->setex($shadowName, $shadowTtl, $shadowData);

                if ( $return === false ) {

                    $this->raiseError( "Error writing cache (PhpRedis), exiting gracefully", array( $this->instance->getLastError() ) );

                    $this->setErrorState();

                }

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        } catch (RedisException $re ) {

            $this->raiseError("Server unreachable (PhpRedis), exiting gracefully", array(
                "RESULTCODE" => $re->getCode(),
                "RESULTMESSAGE" => $re->getMessage()
            ));

            $this->setErrorState();

            return false;

        }

        return $return;

    }

    public function get($name) {

        if ( empty($name) ) {
            
            throw new CacheException("Name of object cannot be empty");
            
        }

        if ( !$this->isEnabled() ) return null;

        $this->resetErrorState();

        try {
            
            $namespace = $this->getNamespaceKey();

            if ( $namespace === false ) {

                $return = false;

            } else {

                $shadowName = $namespace."-".md5($name);

                $return = $this->instance->get($shadowName);

                if ( $return === false ) {

                    $this->raiseError( "Error reading cache (PhpRedis), exiting gracefully", array( $this->instance->getLastError() ) );

                    $this->setErrorState();

                }

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        } catch (RedisException $re ) {

            $this->raiseError("Server unreachable (PhpRedis), exiting gracefully", array(
                "RESULTCODE" => $re->getCode(),
                "RESULTMESSAGE" => $re->getMessage()
            ));

            $this->setErrorState();

            return null;

        }

        return $return === false ? null : unserialize($return);

    }

    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {

            $namespace = $this->getNamespaceKey();

            if ( $namespace === false ) return true;

            if ( empty($name) ) {

                $this->instance->delete($this->getNamespace());

            } else {

                $this->instance->delete($namespace."-".md5($name));

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        } catch (RedisException $re ) {

            $this->raiseError("Server unreachable (PhpRedis), exiting gracefully", array(
                "RESULTCODE" => $re->getCode(),
                "RESULTMESSAGE" => $re->getMessage()
            ));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {

            $this->instance->flushDB();

        } catch (RedisException $re ) {

            $this->raiseError("Server unreachable (PhpRedis), exiting gracefully", array(
                "RESULTCODE" => $re->getCode(),
                "RESULTMESSAGE" => $re->getMessage()
            ));

            $this->setErrorState();

            return false;

        }

        return true;

    }

    public function status() {

        $enabled = $this->isEnabled();

        $objects = 0;

        $options = array();

        $this->resetErrorState();

        try {

            $objects = $this->instance->dbSize();

            $options = $this->instance->info();

        } catch (RedisException $re ) {

            $this->raiseError("Server unreachable (PhpRedis), exiting gracefully", array(
                "RESULTCODE" => $re->getCode(),
                "RESULTMESSAGE" => $re->getMessage()
            ));

            $this->setErrorState();

            $enabled = false;

        }

        return array(
            "provider"  => "phpredis",
            "enabled"   => $enabled,
            "objects"   => $objects,
            "options"   => $options
        );

    }

    public final function getInstance() {

        return $this->instance;

    }

    private function setNamespaceKey() {

        $uId = self::getUniqueId();

        try {

            $return = $this->instance->set($this->getNamespace(), $uId);    

        } catch (RedisException $re ) {

            throw $re;

        }

        return $return === false ? false : $uId;

    }

    private function getNamespaceKey() {

        try {

            $return = $this->instance->get($this->getNamespace());

        } catch (RedisException $re ) {

            throw $re;

        }

        return $return;

    }

}