<?php namespace Comodojo\SimpleCache\Interfaces;

use \Psr\SimpleCache\CacheInterface;
use \Comodojo\SimpleCache\Interfaces\EnhancedSimpleCacheInterface;

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

interface SimpleCacheManagerInterface extends CacheInterface {

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

    public function getNamespace();

    public function setNamespace($namespace);

    public function clearNamespace();

    public function addProvider(EnhancedSimpleCacheInterface $provider, $weight);

    public function removeProvider($id);

    public function getProvider($id);

    public function getProviders($enabled);

    /**
     * get the last selected provider
     *
     * @return
     *
     */
    public function getSelectedProvider();


    /**
     * Get stats from all providers
     *
     * @return CacheItemPoolManagerStats
     */
    public function getStats();

}
