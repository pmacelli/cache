<?php namespace Comodojo\SimpleCache;

use \Comodojo\SimpleCache\Providers\AbstractProvider;
use \Comodojo\SimpleCache\Providers\Vacuum;
use \Comodojo\SimpleCache\Interfaces\SimpleCacheManagerInterface;
use \Comodojo\SimpleCache\Interfaces\EnhancedSimpleCacheInterface;
use \Comodojo\Cache\Traits\NamespaceTrait;
use \Comodojo\Cache\Traits\GenericManagerTrait;
use \Comodojo\SimpleCache\Components\StackManager;
use \Comodojo\Foundation\Validation\DataFilter;
use \Comodojo\SimpleCache\Components\ConfigurationParser;
use \Comodojo\Foundation\Base\Configuration;
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

class Manager extends AbstractProvider implements SimpleCacheManagerInterface {

    use NamespaceTrait, GenericManagerTrait {
        GenericManagerTrait::setNamespace insteadof NamespaceTrait;
    }

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

        $this->logger->info("SimpleCache Manager online; pick mode ".$this->pick_mode);

    }

    public function addProvider(EnhancedSimpleCacheInterface $provider, $weight = 0) {

        return $this->genericAddProvider($provider, $weight);

    }

    public function get($key, $default = null) {

        return $this->selectFrom('GET', $key, $default);

    }

    public function set($key, $value, $ttl = null) {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->set($key, $value, $ttl);
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $pro = $provider[0];

            $provider_result = $pro->set($key, $value, $ttl);

            $this->logger->debug("Saving item $key into provider ".
                $pro->getId().": ".($provider_result ? "OK" : "NOK - ".$pro->getStateMessage()));

            $result[] = $provider_result;

        }

        return !in_array(false, $result);

    }

    public function delete($key) {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->delete($key);
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $result[] = $provider[0]->delete($key);

        }

        return !in_array(false, $result);

    }

    public function getMultiple($keys, $default = null) {

        return $this->selectFrom('GETMULTIPLE', $keys, $default);

    }

    public function setMultiple($values, $ttl = null) {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->setMultiple($values, $ttl);
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $result[] = $provider[0]->setMultiple($values, $ttl);

        }

        return !in_array(false, $result);

    }

    public function deleteMultiple($keys) {

        if ( $this->align_cache === false && $this->pick_mode < 5) {
            return $this->selectProvider()->deleteMultiple($keys);
        }

        $result = [];

        foreach ($this->stack as $provider) {

            $result[] = $provider[0]->deleteMultiple($keys);

        }

        return !in_array(false, $result);

    }

    public function has($key) {

        return $this->selectFrom('HAS', $key);

    }

    public static function createFromConfiguration(Configuration $configuration, LoggerInterface $logger) {

        list($manager_configuration, $providers) = ConfigurationParser::parse($configuration, $logger);

        $manager = new Manager(...$manager_configuration);

        foreach ($providers as $name => $provider) {
            $instance = $provider->instance;
            $weight = $provider->weight;
            $id = $instance->getId();
            $logger->debug("Adding provider $name ($id) to cache manager (w $weight)");
            $manager->addProvider($instance, $weight);
        }

        return $manager;

    }

    protected function selectFrom($mode, $key, $default=null) {

        if ( $this->pick_mode < 5 ) {

            $result = $this->fromSingleProvider($mode, $key, $default);

        } else if ( $this->pick_mode == 5 ) {

            $result = $this->fromAllProviders($mode, $key, $default);

        } else {

            $result = $this->traverse($mode, $key, $default);

        }

        return $result;

    }

    protected function fromSingleProvider($mode, $key, $default) {

        $provider = $this->selectProvider();

        switch ($mode) {

            case 'GET':
                $data = $provider->get($key, $default);
                break;

            case 'GETMULTIPLE':
                $data = $provider->getMultiple($key, $default);
                break;

            case 'HAS':
                $data = $provider->has($key);
                break;

        }

        return $data;

    }

    protected function fromAllProviders($mode, $key, $default) {

        $result = [];

        if ( $mode == 'GET' ) {

            foreach ($this->stack as $provider) {

                $result[] = $provider[0]->get($key, $default);

                // selected provider has no sense in this case
                $this->selected = $provider[0];

            }

            if ( count(array_unique($result)) == 1 ) return $result[0];

            return $default;

        } else if ( $mode == 'GETMULTIPLE' ) {

            foreach ($this->stack as $provider) {

                $result[] = $provider[0]->getMultiple($key, $default);

                // selected provider has no sense in this case
                $this->selected = $provider[0];

            }

            if ( count(array_unique($result)) == 1 ) return $result[0];

            $this->selected = $this->vacuum;

            return $this->vacuum->getMultiple($key, $default);

            // return array_combine($key, array_fill(0, count($key), $default));

        } else {

            foreach ($this->stack as $provider) {

                $result[] = $provider[0]->has($key);

                // selected provider has no sense in this case
                $this->selected = $provider[0];

            }

            return !in_array(false, $result);

        }

    }

    protected function traverse($mode, $key, $default) {

        $this->stack->rewind();

        if ( $mode == 'GET' ) {

            foreach ($this->stack as $provider) {

                $item = $provider[0]->get($key);

                if ( $item !== null ) {

                    // selected provider
                    $this->selected = $provider[0];

                    return $item;

                }

            }

            // selected provider has no sense in this case
            $this->selected = $this->vacuum;

            return $default;

        } else if ( $mode == 'GETMULTIPLE' ) {

            foreach ($this->stack as $provider) {

                $items = $provider[0]->getMultiple($key);

                $item_unique = array_unique($items);

                if ( count($item_unique) > 1 && $item_unique[0] !== null ) {

                    // selected provider
                    $this->selected = $provider[0];

                    return $items;

                }

            }

            // selected provider has no sense in this case
            $this->selected = $this->vacuum;

            return $this->vacuum->getMultiple($key, $default);

        } else {

            foreach ($this->stack as $provider) {

                $item = $provider[0]->has($key);

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
