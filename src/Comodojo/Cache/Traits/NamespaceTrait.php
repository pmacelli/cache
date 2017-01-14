<?php namespace Comodojo\Cache\Traits;

use \Comodojo\Cache\Components\KeyValidator;
use \Comodojo\Exception\InvalidCacheArgumentException;

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
    protected $namespace = "GLOBAL";

    /**
     * {@inheritdoc}
     */
    public function getNamespace() {

        return $this->namespace;

    }

    /**
     * {@inheritdoc}
     */
    public function setNamespace($namespace=null) {

        if ( empty($namespace) ) {

            $this->namespace = "GLOBAL";

        } else if ( KeyValidator::validateKey($namespace) && strlen($namespace) <= 64 ) {

            $this->namespace = strtoupper($namespace);

        } else {

            throw new InvalidCacheArgumentException("Invalid namespace provided");

        }

        return $this;

    }

}
