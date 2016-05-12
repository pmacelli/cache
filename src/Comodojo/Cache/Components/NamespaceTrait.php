<?php namespace Comodojo\Cache\Components;

use \Comodojo\Exception\CacheException;

/**
 * Cache provider interface
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

trait NamespaceTrait {

    /**
     * Determine the current cache scope (default: GLOBAL)
     *
     * @var string
     */
    protected $cache_namespace = "GLOBAL";

    /**
     * {@inheritdoc}
     */
    public function getNamespace() {

        return $this->cache_namespace;

    }

    /**
     * {@inheritdoc}
     */
    public function setNamespace($namespace) {

        if ( preg_match('/^[0-9a-zA-Z]+$/', $namespace) && strlen($namespace) <= 64 ) {

            $this->cache_namespace = strtoupper($namespace);

        } else {

            throw new CacheException("Invalid namespace");

        }

        return $this;

    }

}
