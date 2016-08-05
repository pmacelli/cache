<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Components\StackManager;
use \Comodojo\Cache\Providers\AbstractProvider;
use \Comodojo\Cache\Providers\ProviderInterface;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * Cache manager
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

class Cache extends AbstractProvider {

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

    protected $selector;

    protected $stack;

    protected $provider;

    protected $auto_set_time = false;

    public function __construct($select_mode = null, LoggerInterface $logger = null, $default_ttl = 3600, $flap_interval = 600) {

        $this->selector = filter_var($select_mode, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1,
                'max_range' => 6,
                'default'   => 1
            )
        ));

        $this->stack = new StackManager($flap_interval);

        parent::__construct($logger);

    }

    public function getAutoSetTime() {

        return $this->auto_set_time;

    }

    public function setAutoSetTime($mode=true) {

        $this->auto_set_time = filter_var($mode, FILTER_VALIDATE_BOOLEAN, array(
            'options' => array(
                'default'   => true
            )
        ));

        return $this;

    }

    public function setFlapInterval($ttl) {

        $ttl = filter_var($ttl, FILTER_VALIDATE_INT, array(
            'options' => array(
                'min_range' => 1,
                'default'   => 700
            )
        ));

        return $this->stack->setFlapInterval($ttl);

    }

    public function getFlapInterval() {

        return $this->stack->getFlapInterval();
    }

    public function addProvider(ProviderInterface $provider, $weight = 0) {

        try {

            if ( $provider->isEnabled() === false ) {

                $id = $provider->getCacheId();
                $type = get_class($provider);

                $this->logger->warning("Adding provider $type [$id] as disabled: it will never serve cache requests");

            }

            $provider->setTime($this->getTime())
                ->setTtl($this->getTtl())
                ->setLogger($this->getLogger());

            $id = $this->stack->add($provider, $weight);

        } catch (CacheException $ce) {

            throw $ce;

        }

        return $id;

    }

    public function removeProvider($id) {

        $this->stack->remove($id);

    }

    public function getProvider($id) {

        return $this->stack->get($id);

    }

    public function getProviders($enabled=false) {

        return $enabled ? $this->stack->getAll() : $this->stack->getAll(false);

    }

    public function getSelectedProvider() {

        return $this->provider;

    }

    /* Following methods implement ProviderInterface */

    // public function getNamespace();

    public function setNamespace($namespace = null) {

        parent::setNamespace($namespace);

        foreach ( $this->stack->getAll(false) as $provider ) $provider->setNamespace($namespace);

        return $this;

    }

    public function setTime($time = null) {

        parent::setTime($time);

        foreach ( $this->stack->getAll(false) as $provider ) $provider->setTime($time);

        return $this;

    }

    public function setTtl($ttl = null) {

        parent::setTtl($ttl);

        foreach ( $this->stack->getAll(false) as $provider ) $provider->setTtl($ttl);

        return $this;

    }

    public function setLogger(LoggerInterface $logger = null) {

        parent::setLogger($logger);

        foreach ( $this->stack->getAll(false) as $provider ) $provider->setLogger($logger);

        return $this;

    }

    public function set($name, $data, $ttl = null) {

        if ( !$this->isEnabled() ) return false;

        if ( $this->auto_set_time ) $this->setTime();

        $set = array();

        foreach ($this->stack->getAll() as $id => $provider) {

            $set[] = $provider->set($name, $data, $ttl);

            if ( $provider->getErrorState() ) $this->stack->disable($id);

        };

        return !in_array(false, $set);

    }

    public function get($name) {

        if ( !$this->isEnabled() ) return null;

        if ( $this->auto_set_time ) $this->setTime();

        if ( $this->selector < 5 ) {

            $result = $this->getFromSingleProvider($this->selector, $name);

        } else if ( $this->selector == 5 ) {

            $result = $this->getFromAllProviders($name);

        } else {

            $result = $this->getTraverse($name);

        }

        return $result;

    }

    public function delete($name=null) {

        if ( !$this->isEnabled() ) return false;

        $delete = array();

        foreach ($this->stack->getAll() as $id => $provider) {

            $delete[] = $provider->delete($name);

            if ( $provider->getErrorState() ) $this->stack->disable($id);

        };

        return !in_array(false, $delete);

    }

    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $flush = array();

        foreach ($this->stack->getAll() as $id => $provider) {

            $flush[] = $provider->flush();

            if ( $provider->getErrorState() ) $this->stack->disable($id);

        };

        return !in_array(false, $flush);

    }

    public function status() {

        $return = array();

        if ( !$this->isEnabled() ) {

            foreach ($this->stack->getAll(false) as $id => $provider) {
                $return[] = array(
                    "provider"  => get_class($provider),
                    "enabled"   => false,
                    "objects"   => null,
                    "options"   => array()
                );
            }

        } else {

            foreach ($this->stack->getAll(false) as $id => $provider) {
                $return[] = $provider->status();
            }

        }

        return $return;

    }

    // public function getCacheId();

    // public function getLogger();

    // public function setErrorState($message = null);
    // public function resetErrorState();
    // public function getErrorState();
    // public function getErrorMessage();


    private function getFromSingleProvider($selector, $name) {

        switch ($this->selector) {

            case 1:
                $this->provider = $this->stack->getFirst();
                break;
            case 2:
                $this->provider = $this->stack->getLast();
                break;
            case 3:
                $this->provider = $this->stack->getRandom();
                break;
            case 4:
                $this->provider = $this->stack->getByWeight();
                break;

        }

        $result = $this->provider->get($name);

        if ( $this->provider->getErrorState() ) $this->stack->disable($this->provider->getCacheId());

        return $result;

    }

    private function getFromAllProviders($name) {

        $data = array();

        $providers = $this->stack->getAll();

        foreach ($providers as $id => $provider) {
            $data[] = $provider->get($name);
            if ( $provider->getErrorState() ) $this->stack->disable($provider->getCacheId());
        }

        $values = array_unique(array_map('serialize', $data));

        if (count($values) == 1) {
            return $data[0];
        }

        return null;

    }

    private function getTraverse($name) {

        $data = null;

        $providers = $this->stack->getAll();

        foreach ($providers as $id => $provider) {
            $data = $provider->get($name);
            if ( $provider->getErrorState() ) {
                $this->stack->disable($provider->getCacheId());
            } else {
                if ( $data != null ) return $data;
            }
        }

        return $data;

    }

}
