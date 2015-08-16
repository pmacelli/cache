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
     * administratively enable cache
     *
     */
    public function enable();

    /**
     * administratively disable cache
     *
     */
    public function disable();

    /**
     * check if cache is enabled
     *
     * @return bool
     */
    public function isEnabled();

    /**
     * return the id of the current cache provider
     *
     * @return string
     */
    public function getCacheId();

    /**
     * Set cache element
     *
     * This method will throw only logical exceptions.
     * In case of failures, it will return a boolean false.
     *
     * @param   string  $name    Name for cache element
     * @param   mixed   $data    Data to cache
     * @param   int     $ttl     Time to live
     *
     * @return  bool
     */
    public function set($name, $data, $ttl);

    /**
     * Get cache element
     *
     * This method will throw only logical exceptions.
     * In case of failures, it will return a null value.
     * In case of cache not found, it will return a null value.
     *
     * @param   string  $name    Name for cache element
     *
     * @return  mixed
     */
    public function get($name);

    /**
     * Delete cache object (or entire namespace if $name is null)
     *
     * This method will throw only logical exceptions.
     * In case of failures, it will return a boolean false.
     *
     * @param   string  $name    Name for cache element
     *
     * @return  bool
     */
    public function delete($name);

    /**
     * Set namespace
     *
     * @param   string  $namespace
     *
     * @return  Object  $this
     */
    public function setNamespace($namespace);

    /**
     * Get namespace
     *
     * @return  string
     */
    public function getNamespace();

    /**
     * Clean cache objects in all namespaces
     *
     * This method will throw only logical exceptions.
     *
     * @return  bool
     */
    public function flush();

    /**
     * Get cache status
     *
     * @return  array
     */
    public function status();

    /**
     * Set the logger instance
     *
     */
    public function setLogger(\Monolog\Logger $logger);

    /**
     * Get the current logger instance
     *
     */
    public function getLogger();

    /**
     * Put provider in error state
     *
     */
    public function setErrorState();

    /**
     * Reset error state
     *
     */
    public function resetErrorState();

    /**
     * Check if provider is in error state
     *
     */
    public function getErrorState();

}
