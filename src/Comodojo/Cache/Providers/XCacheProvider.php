<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Components\AbstractProvider;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * XCache cache class
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

class XCacheProvider extends AbstractProvider {

   /**
     * Class constructor
     * 
     * @param LoggerInterface $logger
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct(LoggerInterface $logger=null ) {

        parent::__construct($logger);
        
        if ( self::getXCacheStatus() === false ) {

            $this->logger->error("XCache extension not available, disabling XCacheProvider administratively");

            $this->disable();

        }

    }

    /**
     * {@inheritdoc}
     */
    public function set($name, $data, $ttl=null) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");
        
        if ( is_null($data) ) throw new CacheException("Object content cannot be null");

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {
            
            $this->setTtl($ttl);

            $shadowName = $this->getNamespace()."-".md5($name);
            
            $shadowTtl = $this->ttl;

            $shadowData = serialize($data);

            $return = xcache_set($shadowName, $shadowData, $shadowTtl);

            if ( $return === false ) {

                $this->logger->error("Error writing cache (XCache), exiting gracefully");

                $this->setErrorState();

            }

        } catch (CacheException $ce) {
            
            throw $ce;

        }

        return $return;

    }

    /**
     * {@inheritdoc}
     */
    public function get($name) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");

        if ( !$this->isEnabled() ) return null;

        $this->resetErrorState();

        $shadowName = $this->getNamespace()."-".md5($name);

        $return = xcache_get($shadowName);

        if ( $return === false ) {

            $this->logger->error("Error reading cache (XCache), exiting gracefully");

            $this->setErrorState();

        }

        return is_null($return) ? null : unserialize($return);

    }

    /**
     * {@inheritdoc}
     */
    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        if ( empty($name) ) {

            $delete = xcache_unset_by_prefix($this->getNamespace());

        } else {

            $delete = xcache_unset($this->getNamespace()."-".md5($name));

        }

        if ( $delete === false ) {

            $this->logger->error("Error writing cache (XCache), exiting gracefully");

            $this->setErrorState();

        }


        return $delete;

    }

    /**
     * {@inheritdoc}
     */
    public function flush() {

        if ( !$this->isEnabled() ) return false;

        xcache_clear_cache(XC_TYPE_VAR, -1);

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function status() {

        return array(
            "provider"  => "xcache",
            "enabled"   => $this->isEnabled(),
            "objects"   => @xcache_count(XC_TYPE_VAR),
            "options"   => array()
        );

    }

    /**
     * Check XCache availability
     *
     * @return  bool
     */
    private static function getXCacheStatus() {

        return ( extension_loaded('xcache') && function_exists("xcache_get") );

    }

}