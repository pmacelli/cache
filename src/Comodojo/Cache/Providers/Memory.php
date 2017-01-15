<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Traits\BasicCacheItemPoolTrait;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Comodojo\Cache\Components\KeyValidator;
use \Psr\Cache\CacheItemInterface;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Comodojo\Exception\InvalidCacheArgumentException;
use \Exception;
use \DateTime;

/**
 * A in-memory (array) provider with NO persistence
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

class Memory extends AbstractEnhancedProvider {

    use BasicCacheItemPoolTrait;

    private $data = [];

    public function getItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        if ( !$this->checkMemory($key) ) return new Item($key);

        $namespace = $this->getNamespace();

        $item = new Item($key, true);

        return $item
            ->set($this->data[$namespace][$key]['data'])
            ->expiresAt($this->data[$namespace][$key]['expire']);

    }

    public function hasItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        return $this->checkMemory($key);

    }

    public function clear() {

        $this->data = [];

        return true;

    }

    public function clearNamespace() {

        $namespace = $this->getNamespace();

        if ( $this->checkNamespace() ) {
            unset($this->data[$namespace]);
            return true;
        }

        return false;

    }

    public function deleteItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        if ( !$this->checkMemory($key) ) return false;

        $namespace = $this->getNamespace();

        unset($this->data[$namespace][$key]);

        return true;

    }

    public function save(CacheItemInterface $item) {

        $this->checkNamespace(true);

        $namespace = $this->getNamespace();

        $this->data[$namespace][$item->getKey()] = [
            'data' => $item->getRaw(),
            'expire' => $item->getExpiration()
        ];

        return true;

    }

    public function getStats() {

        return new EnhancedCacheItemPoolStats($this->getId(), 'memory', $this->getState(), count($this->data));

    }

    private function checkNamespace($create = false) {

        $namespace = $this->getNamespace();

        if ( array_key_exists($namespace, $this->data) ) {
            return true;
        } else if ( $create ) {
            $this->data[$namespace] = [];
            return true;
        } else {
            return false;
        }

    }

    private function checkMemory($key) {

        $time = new DateTime('now');

        $namespace = $this->getNamespace();

        if ( !array_key_exists($namespace, $this->data)
            || !array_key_exists($key, $this->data[$namespace]) )
        {
            return false;
        } else if ( $this->data[$namespace][$key]['expire'] === 0
            || $this->data[$namespace][$key]['expire'] > $time )
        {
            return true;
        } else {
            unset($this->data[$namespace][$key]);
            return false;
        }

    }

    public function test() {
        $this->setState(self::CACHE_SUCCESS);
        return true;
    }

}
