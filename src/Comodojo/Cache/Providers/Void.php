<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Components\ItemIterator;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Psr\Cache\CacheItemInterface;
use \Comodojo\Exception\CacheException;
use \Comodojo\Exception\InvalidCacheArgumentException;
use \Exception;

/**
 * A useless null provider
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

class Void extends AbstractEnhancedProvider {

    public function getItem($key) {

        return new Item($key);

    }

    public function getItems(array $keys = []) {

        $items = new ItemsIterator();

        foreach ($keys as $key) {
            $items[$key] = new Item($key);
        }

        return $items;

    }

    public function hasItem($key) {

        return false;

    }

    public function clear() {

        return true;

    }

    public function clearNamespace() {

        return true;

    }

    public function deleteItem($key) {

        return true;

    }

    public function deleteItems(array $keys) {

        return true;

    }

    public function save(CacheItemInterface $item) {

        return true;

    }

    public function saveDeferred(CacheItemInterface $item) {

        return true;

    }

    public function commit() {

        return true;

    }

    public function getStats() {

        return new EnhancedCacheItemPoolStats($this->getId(), 'void');

    }

    public function test() {
        $this->setState(self::CACHE_SUCCESS);
        return true;
    }

}
