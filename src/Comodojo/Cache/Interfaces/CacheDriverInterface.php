<?php namespace Comodojo\Cache\Interfaces;

/**
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

interface CacheDriverInterface {

    public function __construct(array $configuration);

    /**
     * Get the driver name
     *
     * @return string
     */
    public function getName();

    /**
     * Test the driver
     *
     * @return bool
     */
    public function test();

    /**
     * Get item from cache
     *
     * @return string|null
     */
    public function get($key, $namespace);

    /**
     * Save item into cache
     *
     * @return bool
     */
    public function set($key, $namespace, $value, $ttl = null);

    /**
     * Delete item from cache
     *
     * @return bool
     */
    public function delete($key, $namespace);

    /**
     * Clear namespace or whole cache
     *
     * @return bool
     */
    public function clear($namespace = null);

    /**
     * Get multiple items from cache
     *
     * @return array
     */
    public function getMultiple(array $keys, $namespace);

    /**
     * Save multiple items into cache
     *
     * @return bool
     */
    public function setMultiple(array $values, $namespace, $ttl = null);

    /**
     * Delete multiple items from cache
     *
     * @return bool
     */
    public function deleteMultiple(array $keys, $namespace);

    /**
     * Check if item is in cache
     *
     * @return bool
     */
    public function has($key, $namespace);

    /**
     * Get cache driver statistics
     *
     * @return array
     */
    public function stats();

}
