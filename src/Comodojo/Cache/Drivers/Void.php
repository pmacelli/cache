<?php namespace Comodojo\Cache\Drivers;

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

class Void extends AbstractDriver {

    const DRIVER_NAME = "void";

    public function __construct(array $configuration = []) {}

    public function test() {

        return true;

    }

    public function get($key, $namespace) {

        return null;

    }

    public function set($key, $namespace, $value, $ttl = null) {

        return true;

    }

    public function delete($key, $namespace) {

        return false;

    }

    public function clear($namespace = null) {

        return true;

    }

    public function getMultiple(array $keys, $namespace) {

        $keypad = array_combine($keys, array_fill(0, count($keys), null));

    }

    public function setMultiple(array $key_values, $namespace, $ttl = null) {

        return true;

    }

    public function deleteMultiple(array $keys, $namespace) {

        return true;

    }

    public function has($key, $namespace) {

        return false;

    }

    public function stats() {

        return [];

    }

}
