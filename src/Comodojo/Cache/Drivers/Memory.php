<?php namespace Comodojo\Cache\Drivers;

use \DateTime;

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

class Memory extends AbstractDriver {

    const DRIVER_NAME = "memory";

    /**
     * Cache repository
     *
     * @param array
     */
    private $data = [];

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

        return $this->checkMemory($key, $namespace) ?
            $this->data[$namespace][$key]['data'] : null;

    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $namespace, $value, $ttl = null) {

        $this->checkNamespace($namespace, true);

        $expire = is_null($ttl) || $ttl == 0 ? 0 : new DateTime("now + $ttl secs");

        $this->data[$namespace][$key] = [
            'data' => $value,
            'expire' => $expire
        ];

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function delete($key, $namespace) {

        if ( !$this->checkMemory($key, $namespace) ) return false;

        unset($this->data[$namespace][$key]);

        return true;

    }

    /**
     * {@inheritdoc}
     */
    public function clear($namespace = null) {

        if ( $namespace == null ) {

            $this->data = [];

            return true;

        } else {

            if ( $this->checkNamespace($namespace) ) {
                unset($this->data[$namespace]);
                return true;
            }

            return false;

        }

    }

    /**
     * {@inheritdoc}
     */
    public function getMultiple(array $keys, $namespace) {

        $result = [];

        foreach ( $keys as $key ) {
            $result[$key] = $this->get($key, $namespace);
        }

        return $result;

    }

    /**
     * {@inheritdoc}
     */
    public function setMultiple(array $key_values, $namespace, $ttl = null) {

        $result = [];

        foreach ( $key_values as $key => $value ) {
            $result[$key] = $this->set($key, $namespace, $value, $ttl);
        }

        return !in_array(false, $result);

    }

    /**
     * {@inheritdoc}
     */
    public function deleteMultiple(array $keys, $namespace) {

        $result = [];

        foreach ( $keys as $key ) {
            $result[] = $this->delete($key, $namespace);
        }

        return !in_array(false, $result);

    }

    /**
     * {@inheritdoc}
     */
    public function has($key, $namespace) {

        return $this->checkMemory($key, $namespace);

    }

    /**
     * {@inheritdoc}
     */
    public function stats() {

        return ['objects' => count($this->data)];

    }

    private function checkNamespace($namespace, $create = false) {

        if ( array_key_exists($namespace, $this->data) ) {
            return true;
        } else if ( $create ) {
            $this->data[$namespace] = [];
            return true;
        } else {
            return false;
        }

    }

    private function checkMemory($key, $namespace) {

        $time = new DateTime('now');

        if ( !array_key_exists($namespace, $this->data)
            || !array_key_exists($key, $this->data[$namespace]) ) {
            return false;
        } else if ( $this->data[$namespace][$key]['expire'] === 0
            || $this->data[$namespace][$key]['expire'] > $time ) {
            return true;
        } else {
            unset($this->data[$namespace][$key]);
            return false;
        }

    }

}
