<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Providers\AbstractProvider;
use \Comodojo\Cache\Interfaces\CacheItemPoolManagerInterface;
use \Comodojo\Cache\Interfaces\EnhancedCacheItemPoolInterface;
use \Comodojo\Cache\Traits\NamespaceTrait;
use \Comodojo\Cache\Traits\BasicCacheItemPoolTrait;
use \Comodojo\Cache\Components\StackManager;
use \Comodojo\Foundation\Validation\DataFilter;
use \Psr\Cache\CacheItemInterface;
use \ArrayObject;

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

class Manager extends AbstractProvider implements CacheItemPoolManagerInterface {

    use NamespaceTrait;
    use BasicCacheItemPoolTrait;

    /**
     * Select the first (enabled) provider in queue, do not traverse the queue.
     */
    const PICK_FIRST = 1;

    /**
     * Select the last (enabled) provider in queue, do not traverse the queue.
     */
    const PICK_LAST = 2;

    /**
     * Select a random (enabled) provider in queue, do not traverse the queue.
     */
    const PICK_RANDOM = 3;

    /**
     * Select by weight, stop at first enabled provider.
     */
    const PICK_BYWEIGHT = 4;

    /**
     * Ask to all (enabled) providers and match responses.
     */
    const PICK_ALL = 5;

    /**
     * Select the first (enabled) provider, in case of null response traverse
     * the queue.
     */
    const PICK_TRAVERSE = 6;

    const DEFAULT_PICK_MODE = 1;

    protected $pick_mode;

    protected $stack;

    protected $align_cache;

    public function __construct(
        $pick_mode = null,
        LoggerInterface $logger = null,
        $align_cache = true,
        $flap_interval = null
    ) {

        $this->pick_mode = DataFilter::filterInteger($pick_mode, 1, 6, self::DEFAULT_PICK_MODE);

        $this->align_cache = DataFilter::filterBoolean($align_cache, true);

        $stack = new ArrayObject([]);

        $this->stack = new StackManager($stack->getIterator());
        $this->stack->setFlapInterval($flap_interval);

        parent::__construct($logger);

    }

    public function addProvider(EnhancedCacheItemPoolInterface $provider, $weight = 0) {

        $this->stack->add($provider, $weight);

        return $this;

    }

    public function removeProvider($id) {

        return $this->stack->remove($id);

    }

    public function getProvider($id) {

        return $this->stack->get($id);

    }

    public function getProviders($enabled = false) {

        return $this->stack->getAll($enabled);

    }

    public function getSelectedProvider() {

        return $this->stack->getCurrent();

    }

    public function getItem($key) {

        return $this->selectFrom('GET', $key);

    }

    public function hasItem($key) {

        return $this->selectFrom('HAS', $key);

    }

    public function clear() {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->clear();
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $result[] = $provider[0]->clear();

        }

        return !in_array(false, $result);

    }

    public function deleteItem($key) {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->deleteItem($key);
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $result[] = $provider[0]->deleteItem($key);

        }

        return !in_array(false, $result);

    }

    public function save(CacheItemInterface $item) {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->save($item);
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $result[] = $provider[0]->save($item);

        }

        return !in_array(false, $result);

    }

    public function setNamespace($namespace = null) {

        foreach ($this->stack->getAll(false) as $provider) {
            $provider->setNamespace($namespace);
        }

        $this->namespace = empty($namespace) ? "GLOBAL" : $namespace;

        return $this;

    }

    public function clearNamespace() {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->clearNamespace();
        }

        $result = [];

        foreach ($this->stack->getAll() as $provider) {
            $result[] = $provider->clearNamespace();
        }

        return !in_array(false, $result);

    }

    public function getStats() {

        $stats = [];

        foreach ($this->stack->getAll(false) as $provider) {
            $stats[] = $provider->getStats();
        }

        return $stats;

    }

    protected function selectProvider() {

        switch ($this->pick_mode) {

            case 1:
                $provider = $this->stack->getFirstProvider();
                break;
            case 2:
                $provider = $this->stack->getLastProvider();
                break;
            case 3:
                $provider = $this->stack->getRandomProvider();
                break;
            case 4:
                $provider = $this->stack->getHeavyProvider();
                break;

        }

        return $provider;

    }

    protected function selectFrom($mode, $key) {

        if ( $this->pick_mode < 5 ) {

            $result = $this->fromSingleProvider($mode, $key);

        } else if ( $this->pick_mode == 5 ) {

            $result = $this->fromAllProviders($mode, $key);

        } else {

            $result = $this->traverse($key);

        }

        return $result;

    }

    protected function fromSingleProvider($mode, $key) {

        $provider = $this->selectProvider();

        if ( $mode == 'HAS' ) return $provider->hasItem($key);

        return $provider->getItem($key);

    }

    protected function fromAllProviders($mode, $key) {

        $result = [];

        if ( $mode == 'GET' ) {

            foreach ($this->stack as $provider) {

                $result[] = $provider[0]->getItem($key);

            }

            if ( count(array_unique($result)) == 1 ) return $result[0];

            return new Item($key);

        } else {

            foreach ($this->stack as $provider) {

                $result[] = $provider[0]->hasItem($key);

            }

            return !in_array(false, $result);

        }

    }

    protected function traverse($key) {

        $this->stack->rewind();

        foreach ($this->stack as $provider) {

            $item = $provider[0]->getItem($key);

            if ( $item->isHit() ) return $item;

        }

        return new Item($key);

    }

}
