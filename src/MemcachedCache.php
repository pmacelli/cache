<?php namespace Comodojo\Cache;

use \Comodojo\Cache\CacheInterface\CacheInterface;
use \Comodojo\Cache\CacheObject\CacheObject;
use \Memcached;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * Memcached cache class
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

class MemcachedCache extends CacheObject implements CacheInterface {

    private $instance = null;

    public function __construct( $server, $port, $weight=0 ) {

        $this->instance = new Memcached();

        if ( empty($server) ) {

            // if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Invalid or unspecified memcached server");
            
            throw new CacheException("Invalid or unspecified memcached server");

        }

        $port = filter_var($port, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 1,
                "max_range" => 65535,
                "default" => 11211 )
            )
        );

        $weight = filter_var($weight, FILTER_VALIDATE_INT, array(
            "options" => array(
                "min_range" => 0,
                "default" => 0 )
            )
        );

        $this->addServer($server, $port, $weight);

    }

    public function set($name, $data, $ttl=null) {

        if ( empty($name) ) {
            
            // if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Name of object cannot be empty");
            
            throw new CacheException("Name of object cannot be empty");
            
        }
        
        if ( is_null($data) ) {
            
            // if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError("Object cannot be empty");
            
            throw new CacheException("Object content cannot be null");
            
        }

        if ( !$this->isEnabled() ) return false;

        try {
            
            $this->setTtl($ttl);

            $namespace = $this->getNamespaceKey();

            if ( $namespace === false ) $namespace = $this->setNamespaceKey();

            if ( $namespace === false ) {

                $this->raiseError("Error writing cache, exiting gracefully", array(
                    "RESULTCODE" => $this->instance->getResultCode(),
                    "RESULTMESSAGE" => $this->instance->getResultMessage()
                ));

                $return = false;

            } else {

                $shadowName = $namespace."-".md5($name);
            
                $shadowTtl = $this->getTime() + $this->ttl;
                
                $return = $this->instance->set($shadowName, $data, $shadowTtl);

                if ( $return === false ) {

                    $this->raiseError("Error writing cache, exiting gracefully", array(
                        "RESULTCODE" => $this->instance->getResultCode(),
                        "RESULTMESSAGE" => $this->instance->getResultMessage()
                    ));

                }

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $return;

    }

    public function get($name) {

        if ( empty($name) ) {
            
            throw new CacheException("Name of object cannot be empty");
            
        }

        if ( !$this->isEnabled() ) return null;

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) {

            $return = null;

        } else {

            $shadowName = $namespace."-".md5($name);

            $return = $this->instance->get($shadowName);

            if ( $return === false AND $this->instance->getResultCode() != Memcached::RES_NOTFOUND ) {

                $this->raiseError("Error reading cache, exiting gracefully", array(
                    "RESULTCODE" => $this->instance->getResultCode(),
                    "RESULTMESSAGE" => $this->instance->getResultMessage()
                ));

            }

        }

        return $return === false ? null : $return;

    }

    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;

        $namespace = $this->getNamespaceKey();

        if ( $namespace === false ) return true;

        if ( empty($name) ) {

            $delete = $this->instance->delete($this->getNamespace());

        } else {

            $delete = $this->instance->delete($namespace."-".$name);

        }

        if ( $delete === false ) {

            $this->raiseError("Error writing cache, exiting gracefully", array(
                "RESULTCODE" => $this->instance->getResultCode(),
                "RESULTMESSAGE" => $this->instance->getResultMessage()
            ));

        }


        return $delete;

    }

    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $result = $this->instance->flush();

        if ( $result === false ) {

            $this->raiseError("Error flushing cache", array(
                "RESULTCODE" => $this->instance->getResultCode(),
                "RESULTMESSAGE" => $this->instance->getResultMessage()
            ));

            return false;

        }

        return true;

    }

    public function status( /*$currentScope=false*/ ) {

        $stats = $this->instance->getStats();

        $objects = 0;

        foreach ($stats as $key => $value) {
            
            $objects = max($objects, $value['curr_items']);

        }

        return array(
            "provider"  => "memcached",
            "enabled"   => $this->isEnabled(),
            "objects"   => $objects,
            "options"   => $stats
        );

    }

    public final function getInstance() {

        return $this->instance;

    }

    private function setNamespaceKey($namespace) {

        $uId = self::getUniqueId();

        $return = $this->instance->set($namespace, $uId, 0);

        return $return === false ? false : $uId;

    }

    private function getNamespaceKey($namespace) {

        $return = $this->instance->get($namespace);

    }

    private function addServer($server, $port, $weight) {

        $this->raiseError("Error communicating with server", array(
            "RESULTCODE" => $this->instance->getResultCode(),
            "RESULTMESSAGE" => $this->instance->getResultMessage()
        ));

        if ( sizeof($this->instance->getServerList()) == 0 ) {

            $this->raiseError("No available server, disabling cache administratively");

            $this->disable();

        } else {

            $this->enable();

        }

    }

    static private function getUniqueId() {

        return substr(md5(uniqid(rand(), true)), 0, 64);

    }

}