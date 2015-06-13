<?php namespace Comodojo\Cache\CacheInterface;

/**
 * Object cache interface
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

interface CacheInterface {

    /**
     * Set cache element
     *
     * @param   string  $name    Name for cache element
     * @param   mixed   $data    Data to cache
     * @param   int     $ttl     Time to live
     *
     * @return  Object  $this
     */
    public function set($name, $data, $ttl);

    /**
     * Get cache element
     *
     * @param   string  $name    Name for cache element
     *
     * @return  Object  $this
     */
    public function get($name);

    /**
     * Set scope
     *
     * @param   string  $scope
     *
     * @return  Object  $this
     */
    public function setScope($scope);

    /**
     * Get scope
     *
     * @return  string
     */
    public function getScope();

    /**
     * Flush cache (or entire scope)
     *
     * @param   string  $name    Name for cache element
     *
     * @return  bool
     */
    public function flush($name);

    /**
     * Clean cache objects in any scope
     *
     * @return  bool
     */
    public function purge();

    /**
     * Cache status
     *
     * @return  array
     */
    public function status();

}
