<?php namespace Comodojo\Cache\Interfaces;

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

interface CacheDriverInterface {

    /**
     * Get the driver name
     *
     * @return string
     */
    public function getName();

    /**
     * Test the driver
     *
     * The behaiour of this method may differ fro one driver to another.
     * It could forward, for example, a dependency test, a in-cache test or both.
     *
     * @return bool
     */
    public function test();

    /**
     * Get item from cache
     *
     * @param string $key The key
     * @param string $namespace Namespace where $key resides
     * @return string|null
     */
    public function get($key, $namespace);

    /**
     * Save item into cache
     *
     * @param string $key The key
     * @param string $namespace Namespace where $key resides
     * @param mixed $value Value
     * @param int $ttl Time to live of cache object (in seconds)
     * @return bool
     */
    public function set($key, $namespace, $value, $ttl = null);

    /**
     * Delete item from cache
     *
     * @param string $key The key
     * @param string $namespace Namespace where $key resides
     * @return bool
     */
    public function delete($key, $namespace);

    /**
     * Clear namespace or whole cache
     *
     * @param string $namespace Namespace to clean or null to purge whole cache.
     * @return bool
     */
    public function clear($namespace = null);

    /**
     * Get multiple items from cache
     *
     * @param array $keys Array of keys
     * @param string $namespace Namespace where $keys reside
     * @return array
     */
    public function getMultiple(array $keys, $namespace);

    /**
     * Save multiple items into cache
     *
     * @param array $values Associative array of keys and values
     * @param string $namespace Namespace where $keys reside
     * @param int $ttl Time to live of cache objects (in seconds)
     * @return bool
     */
    public function setMultiple(array $values, $namespace, $ttl = null);

    /**
     * Delete multiple items from cache
     *
     * @param array $keys Array of keys
     * @param string $namespace Namespace where $keys reside
     * @return bool
     */
    public function deleteMultiple(array $keys, $namespace);

    /**
     * Check if item is in cache
     *
     * @param array $key The key
     * @param string $namespace Namespace where $key resides
     * @return bool
     */
    public function has($key, $namespace);

    /**
     * Get cache driver statistics
     *
     * The behaiour of this method may differ fro one driver to another.
     * Providers will transform data in a EnhancedCacheItemPoolStats object.
     *
     * @return array
     */
    public function stats();

}
