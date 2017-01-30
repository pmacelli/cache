<?php namespace Comodojo\Cache\Drivers;

use \Comodojo\Cache\Interfaces\CacheDriverInterface;
use \Comodojo\Cache\Components\UniqueId;
use \Exception;

/**
 * Apcu provider
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

abstract class AbstractDriver implements CacheDriverInterface {

    const DRIVER_NAME = "";

    abstract public function __construct(array $configuration);

    public function getName() {

        return static::DRIVER_NAME;

    }

    abstract public function test();

    abstract public function get($key, $namespace);

    abstract public function set($key, $namespace, $value, $ttl = null);

    abstract public function delete($key, $namespace);

    abstract public function clear($namespace = null);

    abstract public function getMultiple(array $keys, $namespace);

    abstract public function setMultiple(array $key_values, $namespace, $ttl = null);

    abstract public function deleteMultiple(array $keys, $namespace);

    abstract public function has($key, $namespace);

    abstract public function stats();

}
