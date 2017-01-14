<?php namespace Comodojo\Cache\Interfaces;

use \Psr\Cache\CacheItemPoolInterface;
use \Comodojo\Cache\Interfaces\EnhancedCacheItemPoolInterface;

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

interface CacheItemPoolManagerInterface extends CacheItemPoolInterface {

    public function getNamespace();

    public function setNamespace($namespace);

    public function clearNamespace();

    public function addProvider(EnhancedCacheItemPoolInterface $provider, $weight);

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
