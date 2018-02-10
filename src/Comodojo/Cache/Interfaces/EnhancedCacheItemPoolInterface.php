<?php namespace Comodojo\Cache\Interfaces;

use \Psr\Cache\CacheItemPoolInterface;

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

interface EnhancedCacheItemPoolInterface extends CacheItemPoolInterface {

    const CACHE_SUCCESS = 0;

    const CACHE_ERROR = 1;

    /**
     * Returns the internal pool's id
     *
     * @return bool
     */
    public function getId();

    /**
     * Returns the current state
     *
     * @return bool True if no error.
     */
    public function getState();

    /**
     * Returns the current state
     *
     * @return string|null Last error message (if any).
     */
    public function getStateMessage();

    /**
     * Returns the time when the state was determined
     *
     * @return DateTimeInterface|null
     */
    public function getStateTime();

    /**
     * Put provider in error state
     *
     * @param bool $status Current status
     * @param string $message Relative error message (if any)
     * @return static The invoked object.
     */
    public function setState($state, $message = null);

    /**
     * Test the pool
     *
     * Test should be used to ensure the status flag is setted correctly.
     * If test passes, then status should be CACHE_SUCCESS, otherwise it
     * should correspond to CACHE_ERROR
     *
     * @return bool
     */
    public function test();

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
     * Get provider statistics
     *
     * @return EnhancedCacheItemPoolStats
     */
    public function getStats();

}
