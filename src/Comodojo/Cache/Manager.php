<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Providers\AbstractProvider;
use \Comodojo\Cache\Providers\Vacuum;
use \Comodojo\Cache\Interfaces\CacheItemPoolManagerInterface;
use \Comodojo\Cache\Interfaces\EnhancedCacheItemPoolInterface;
use \Comodojo\Cache\Traits\NamespaceTrait;
use \Comodojo\Cache\Traits\BasicCacheItemPoolTrait;
use \Comodojo\Cache\Traits\GenericManagerTrait;
use \Comodojo\Cache\Components\StackManager;
use \Comodojo\Foundation\Validation\DataFilter;
use \Psr\Cache\CacheItemInterface;
use \Psr\Log\LoggerInterface;
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

    use NamespaceTrait, GenericManagerTrait {
        GenericManagerTrait::setNamespace insteadof NamespaceTrait;
    }
    use BasicCacheItemPoolTrait;

    const DEFAULT_PICK_MODE = 1;

    protected $pick_mode;

    protected $stack;

    protected $align_cache;

    protected $vacuum;

    protected $selected;

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

        $this->vacuum = new Vacuum($this->logger);

        $this->logger->info("Cache manager online; pick mode ".$this->pick_mode);

    }

    public function addProvider(EnhancedCacheItemPoolInterface $provider, $weight = 0) {

        return $this->genericAddProvider($provider, $weight);

    }

    public function getItem($key) {

        return $this->selectFrom('GET', $key);

    }

    public function hasItem($key) {

        return $this->selectFrom('HAS', $key);

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

            $pro = $provider[0];

            $provider_result = $pro->save($item);

            $this->logger->debug("Saving item ".$item->getKey()." into provider ".
                $pro->getId().": ".($provider_result ? "OK" : "NOK - ".$pro->getStateMessage()));

            $result[] = $provider_result;

        }

        return !in_array(false, $result);

    }

    protected function selectFrom($mode, $key) {

        if ( $this->pick_mode < 5 ) {

            $result = $this->fromSingleProvider($mode, $key);

        } else if ( $this->pick_mode == 5 ) {

            $result = $this->fromAllProviders($mode, $key);

        } else {

            $result = $this->traverse($mode, $key);

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

                // selected provider has no sense in this case
                $this->selected = $provider[0];

            }

            if ( count(array_unique($result)) == 1 ) return $result[0];

            return new Item($key);

        } else {

            foreach ($this->stack as $provider) {

                $result[] = $provider[0]->hasItem($key);

                // selected provider has no sense in this case
                $this->selected = $provider[0];

            }

            return !in_array(false, $result);

        }

    }

    protected function traverse($mode, $key) {

        $this->stack->rewind();

        if ( $mode == 'GET' ) {

            foreach ($this->stack as $provider) {

                $item = $provider[0]->getItem($key);

                if ( $item->isHit() ) {

                    // selected provider has no sense in this case
                    $this->selected = $provider[0];

                    return $item;

                }

            }

            // selected provider has no sense in this case
            $this->selected = $this->vacuum;

            return new Item($key);

        } else {

            foreach ($this->stack as $provider) {

                $item = $provider[0]->hasItem($key);

                if ( $item === true ) {

                    // selected provider has no sense in this case
                    $this->selected = $provider[0];

                    return true;

                }

            }

            // selected provider has no sense in this case
            $this->selected = $this->vacuum;

            return false;

        }

    }

}
