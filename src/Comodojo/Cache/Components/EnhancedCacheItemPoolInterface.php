<?php namespace Comodojo\Cache\Components;

use \Psr\Cache\CacheItemPoolInterface;

/**
 * CacheItemPoolInterface extension to handle it's state
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
     * @return bool
     *   True if no error.
     */
    public function getState();

    /**
     * Returns the current state
     *
     * @return string|null
     *   Last error message (if any).
     */
    public function getStateMessage();

    /**
     * Returns the time when the state was fixed
     *
     * @return DateTimeInterface|null
     *
     */
    public function getStateTime();

    /**
     * Set provider in error state
     *
     * @param bool $status
     *   Current status
     *
     * @param string $message
     *   Relative error message (if any)
     *
     * @return static
     *   The invoked object.
     */
    public function setState($state, $message = null);

    public function test();

    public function getNamespace();

    public function setNamespace($namespace);

    public function clearNamespace();

    /**
     * Disable provider
     *
     * @return EnhancedCacheItemPoolStats
     */
    public function getStats();

}
