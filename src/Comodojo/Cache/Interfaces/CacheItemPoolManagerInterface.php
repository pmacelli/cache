<?php namespace Comodojo\Cache\Interfaces;

use \Psr\Cache\CacheItemPoolInterface;
use \Comodojo\Cache\Interfaces\EnhancedCacheItemPoolInterface;

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

interface CacheItemPoolManagerInterface extends CacheItemPoolInterface {

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

    /**
     * Get current namespace
     *
     * @return string
     */
    public function getNamespace();

    /**
     * Set current namespace
     *
     * @param string $namespace Namespace where $key resides
     * @return string|null
     */
    public function setNamespace($namespace);

    /**
     * Chear the whole (current) namespace
     *
     * @return string|null
     */
    public function clearNamespace();

    /**
     * Add a new provider to the manager's stack
     *
     * @param EnhancedCacheItemPoolInterface $provider
     * @param int $weight
     * @return int
     */
    public function addProvider(EnhancedCacheItemPoolInterface $provider, $weight);

    /**
     * Remove provider from the manager's stack
     *
     * @param int $id
     * @return bool
     */
    public function removeProvider($id);

    /**
     * Get a registered provider
     *
     * @param int $id
     * @return EnhancedCacheItemPoolInterface
     */
    public function getProvider($id);

    /**
     * Get registered providers
     *
     * @param int $enabled If true, only enabled providers will be returned
     * @return array
     */
    public function getProviders($enabled);

    /**
     * Get the last selected provider
     *
     * @return EnhancedCacheItemPoolInterface
     */
    public function getSelectedProvider();

    /**
     * Get stats from all providers
     *
     * @return CacheItemPoolManagerStats
     */
    public function getStats();

}
