<?php namespace Comodojo\Cache\Components;

use \Comodojo\Cache\Traits\FlapIntervalTrait;
use \Exception;
use \FilterIterator;

/**
 * @package     Comodojo Cache
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

class AbstractStackManager extends FilterIterator {

    use FlapIntervalTrait;

    public function accept() {

        $provider = $this->getInnerIterator()->current();
        $provider = $provider[0];

        $status = $provider->getState();

        if (
            $status === $provider::CACHE_ERROR &&
            date_create('now')->diff($provider->getStateTime())->format('%s') > $this->getFlapInterval()
        ) {

            return $provider->test();

        }

        return $status == $provider::CACHE_SUCCESS ? true : false;

    }

    public function genericAdd($provider, $weight) {

        $pools = $this->getInnerIterator();

        $id = $provider->getId();

        $pools[$id] = [$provider, $weight];

    }

    public function remove($id) {

        $pools = $this->getInnerIterator();

        if ( isset($pools[$id]) ) {
            unset($pools[$id]);
            return true;
        }

        throw new Exception("Provider $id not registered into the stack");

    }

    public function get($id) {

        $pools = $this->getInnerIterator();

        if ( isset($pools[$id]) ) return $pools[$id][0];

        throw new Exception("Provider $id not registered into the stack");

    }

    public function getAll($enabled = false) {

        $result = [];

        if ( $enabled === true ) {

            foreach ( $this as $id => $provider ) $result[$id] = $provider[0];

        } else {

            foreach ( $this->getInnerIterator() as $id => $provider ) $result[$id] = $provider[0];

        }

        return $result;

    }

    public function getCurrent() {

        $current = $this->current();
        return $current[0];

    }

    public function has($id) {

        $pools = $this->getInnerIterator();

        return isset($pools[$id]);

    }

    public function getRandomProvider() {

        $stack = $this->getAll(true);

        $rand = array_rand($stack);

        return $rand === null ? null : $stack[$rand];

    }

    public function getFirstProvider() {

        $this->rewind();

        $current = $this->current();

        return $current === null ? null : $current[0];

        // $providers = $this->getAll();

        // return current($providers);

    }

    public function getLastProvider() {

        $this->rewind();

        $providers = $this->getAll(true);

        $provider = end($providers);

        return $provider;

    }

    public function getHeavyProvider() {

        $providers = $this->getAll(true);

        if ( count($providers) === 0 ) return null;

        $weights = $this->getWeights();

        asort($weights);

        end($weights);

        return $providers[key($weights)];

    }

    private function getWeights() {

        $result = [];

        foreach ( $this as $id => $provider ) $result[$id] = $provider[1];

        return $result;

    }

}
