<?php namespace Comodojo\Cache\Components;

use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * Abstract Manager class
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

abstract class AbstractManager extends AbstractProvider {

    const PICK_FIRST = 1;
    const PICK_LAST = 2;
    const PICK_RANDOM = 3;
    const PICK_BYWEIGHT = 4;
    const PICK_ALL = 5;

    protected $caches = array();

    protected $cache_weights = array();

    protected $selector;

    protected $selected_cache;

    public function __construct($select_mode = null, LoggerInterface $logger = null) {

        $this->selector = filter_var($select_mode, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1,
                'max_range' => 5,
                'default'   => 1
            )
        ));

        parent::__construct($logger);

    }

    public function setNamespace($namespace) {

        $return = parent::setNamespace($namespace);

        foreach ( $this->caches as $cache ) $cache->setNamespace($namespace);

        return $return;

    }

    public function enable() {

        $return = parent::enable();

        foreach ( $this->caches as $cache ) $cache->enable();

        return $return;

    }

    public function disable() {

        $return = parent::disable();

        foreach ( $this->caches as $cache ) $cache->disable();

        return $return;

    }

    public function setTime($time = null) {

        $return = parent::setTime($time);

        foreach ( $this->caches as $cache ) $cache->setTime($time);

        return $return;

    }

    public function setTtl($ttl = null) {

        $return = parent::setTtl($ttl);

        foreach ( $this->caches as $cache ) $cache->setTtl($ttl);

        return $return;

    }

    public function setLogger(LoggerInterface $logger = null) {

        $return = parent::setLogger($logger);

        foreach ( $this->caches as $cache ) $cache->setLogger($logger);

        return $return;

    }

    public function addProvider(ProviderInterface $cache_provider, $weight = 0) {

        $corrected_weight = filter_var($weight, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 0,
                'max_range' => 100,
                'default'   => 0
            )
        ));

        $cache_id = $cache_provider->getCacheId();

        if ( array_key_exists($cache_id, $this->caches) ) throw new CacheException("Provider $cache_id already registered");

        $cache_provider->setTime($this->getTime())
            ->setTtl($this->getTtl())
            ->setLogger($this->getLogger());

        $this->caches[$cache_id] = $cache_provider;

        $this->cache_weights[$cache_id] = $corrected_weight;

        return $cache_id;

    }

    public function removeProvider($cache_id) {

        if ( array_key_exists($cache_id, $this->caches) && array_key_exists($cache_id, $this->cache_weights) ) {

            unset($this->caches[$cache_id]);

            unset($this->cache_weights[$cache_id]);

        } else {

            throw new CacheException("Provider not registered");

        }

        return true;

    }

    public function getProvider($cache_id) {

        if ( array_key_exists($cache_id, $this->caches) && array_key_exists($cache_id, $this->cache_weights) ) {

            return $this->caches[$cache_id];

        }

        throw new CacheException("Provider not registered");

    }

    public function getProviders($type = null) {

        $providers = array();

        if ( is_null($type) ) {

            foreach ( $this->caches as $id => $cache ) $providers[$id] = $cache;

        } else {

            foreach ( $this->caches as $id => $cache ) {

                $provider_class = get_class($cache);

                if ( $provider_class == $type ) $providers[$id] = $cache;

            }

        }

        return $providers;

    }

    public function getSelectedProvider() {

        return $this->selected_cache;

    }

    public function getSelectedCache() {

        $this->logger->notice("Use of getSelectedCache is deprecated and will be removed in next major version, please use getSelectedProvider instead.");

        return $this->getSelectedProvider();

    }

}
