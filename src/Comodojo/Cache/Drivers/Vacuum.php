<?php namespace Comodojo\Cache\Drivers;

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

class Vacuum extends AbstractDriver {

    const DRIVER_NAME = "vacuum";

    /**
     * {@inheritdoc}
     */
    public function __construct(array $configuration = []) {}

        /**
         * {@inheritdoc}
         */
        public function test() {

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function get($key, $namespace) {

        return null;

    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $namespace, $value, $ttl = null) {

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, $namespace) {

        return false;

    }

    /**
     * {@inheritdoc}
     */
    public function clear($namespace = null) {

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $namespace) {

        return array_combine($keys, array_fill(0, count($keys), null));

    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(array $key_values, $namespace, $ttl = null) {

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(array $keys, $namespace) {

        return false;

    }

    /**
     * {@inheritdoc}
     */
    public function has($key, $namespace) {

        return false;

    }

    /**
     * {@inheritdoc}
     */
    public function stats() {

        return [];

    }

}
