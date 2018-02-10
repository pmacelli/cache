<?php namespace Comodojo\Cache\Traits;

use \Comodojo\Cache\Components\ItemsIterator;
use \Psr\Cache\CacheItemInterface;

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

trait BasicCacheItemPoolTrait {

    private $queue = [];

    public function getItems(array $keys = []) {

        $items = [];

        foreach ( $keys as $key ) {

            $items[$key] = $this->getItem($key);
        }

        return $items;

    }

    public function deleteItems(array $keys) {

        $result = [];

        foreach ( $keys as $key ) {

            $result[] = $this->deleteItem($key);

        }

        return in_array(false, $result) ? false : true;

    }

    public function saveDeferred(CacheItemInterface $item) {

        $this->checkQueueNamespace(true);

        $namespace = $this->getNamespace();

        $this->queue[$namespace][$item->getKey()] = $item;

        return true;

    }

    public function commit() {

        $result = [];

        $active_namespace = $this->getNamespace();

        foreach ( $this->queue as $namespace => $queue ) {

            $this->setNamespace($namespace);

            foreach ( $queue as $key => $item ) {

                $result[] = $this->save($item);

            }

        }

        $this->queue = [];

        $this->setNamespace($active_namespace);

        return in_array(false, $result) ? false : true;

    }

    private function checkQueueNamespace($create = false) {

        $namespace = $this->getNamespace();

        if ( array_key_exists($namespace, $this->queue) ) {
            return true;
        } else if ( $create ) {
            $this->queue[$namespace] = [];
            return true;
        } else {
            return false;
        }

    }

}
