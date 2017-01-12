<?php namespace Comodojo\Cache\Components;

use \Comodojo\Cache\Interfaces\EnhancedCacheItemPoolInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
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

class StackManager {

    private $stack = [];

    private $weights = [];

    private $failures = [];

    private $flap_interval = 600;

    public function __construct($flap_interval) {

        $this->flap_interval = $flap_interval;

    }

    public function getFlapInterval() {

        return $this->flap_interval;

    }

    public function setFlapInterval($ttl) {

        $this->flap_interval = $ttl;

        return $this;

    }

    public function add(EnhancedCacheItemPoolInterface $provider, $weight = 0) {

        $id = $provider->getCacheId();

        if ( array_key_exists($id, $this->stack) ) throw new CacheException("Provider $id already registered");

        $this->stack[$id] = $provider;

        $this->weights[$id] = $weight;

        return $id;

    }

    public function remove($id) {

        if ( array_key_exists($id, $this->stack) && array_key_exists($id, $this->weights) ) {

            unset($this->stack[$id]);

            unset($this->weights[$id]);

            if ( array_key_exists($id, $this->failures) ) unset($this->failures[$id]);

        } else {

            throw new CacheException("Provider not registered");

        }

    }

    public function get($id) {

        if ( array_key_exists($id, $this->stack) && array_key_exists($id, $this->weights) ) {

            return $this->stack[$id];

        }

        throw new CacheException("Provider not registered");

    }

    public function disable($id) {

        if ( array_key_exists($id, $this->stack) && array_key_exists($id, $this->weights) ) {

            $this->stack[$id]->disable();

            $this->failures[$id] = time();

        } else {

            throw new CacheException("Provider not registered");

        }

    }

    public function enable($id) {

        if ( array_key_exists($id, $this->stack) && array_key_exists($id, $this->weights) ) {

            $this->stack[$id]->enable();

            if ( array_key_exists($id, $this->failures) ) unset($this->failures[$id]);

        } else {

            throw new CacheException("Provider not registered");

        }

    }

    public function getAll($enabled=true) {

        return $enabled ? $this->getEnabled() : $this->stack;

    }

    public function getRandom() {

        return $this->stack[array_rand($this->getEnabled())];

    }

    public function getFirst() {

        $providers = $this->getEnabled();

        if ( empty($providers) ) return null;

        reset($providers);

        return current($providers);

    }

    public function getLast() {

        $providers = $this->getEnabled();

        if ( empty($providers) ) return null;

        reset($providers);

        return end($providers);

    }

    public function getByWeight() {

        $providers = $this->getEnabled();

        $weights = array_intersect_key($this->weights, $providers);

        asort($weights);

        end($weights);

        return $providers[key($weights)];

    }

    private function checkCacheFlap($cache_id, $cache) {

        if ( $cache->isEnabled() === false && array_key_exists($cache_id, $this->failures) ) {

            if ( $this->failures[$cache_id] < time() + $this->flap_interval ) {
                $cache->enable();
                unset($this->failures[$cache_id]);
            }

        }

    }

    private function getEnabled() {

        $return = array();

        foreach ($this->stack as $id => $cache) {
            $this->checkCacheFlap($id, $cache);
            if ( $cache->isEnabled() ) $return[$id] = $cache;
        }

        return $return;

    }

}
