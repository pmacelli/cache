<?php namespace Comodojo\Cache\Drivers;

use \Comodojo\Cache\Interfaces\CacheDriverInterface;

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

abstract class AbstractDriver implements CacheDriverInterface {

    const DRIVER_NAME = "";

    /**
     * Class constructor
     *
     * @param array $configuration
     * @return self
     */
    abstract public function __construct(array $configuration);

    /**
     * {@inheritdoc}
     */
    public function getName() {

        return static::DRIVER_NAME;

    }

    /**
     * {@inheritdoc}
     */
    abstract public function test();

    /**
     * {@inheritdoc}
     */
    abstract public function get($key, $namespace);

    /**
     * {@inheritdoc}
     */
    abstract public function set($key, $namespace, $value, $ttl = null);

    /**
     * {@inheritdoc}
     */
    abstract public function delete($key, $namespace);

    /**
     * {@inheritdoc}
     */
    abstract public function clear($namespace = null);

    /**
     * {@inheritdoc}
     */
    abstract public function getMultiple(array $keys, $namespace);

    /**
     * {@inheritdoc}
     */
    abstract public function setMultiple(array $key_values, $namespace, $ttl = null);

    /**
     * {@inheritdoc}
     */
    abstract public function deleteMultiple(array $keys, $namespace);

    /**
     * {@inheritdoc}
     */
    abstract public function has($key, $namespace);

    /**
     * {@inheritdoc}
     */
    abstract public function stats();

}
