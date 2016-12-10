<?php namespace Comodojo\Cache\Components;

use \Psr\Cache\CacheItemInterface;
use \Comodojo\Exception\CacheException;

/**
 * @package     Comodojo Foundation
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

trait CacheItemsArrayAccessTrait {

    /**
     * Assigns a value to index offset
     *
     * @param string $index The offset to assign the value to
     * @param mixed  $value The value to set
     */
     public function offsetSet($index, $value) {

        if ( ($value instanceof CacheItemInterface) == false ) {
            throw new CacheException("Item value is not an instance of \Psr\Cache\CacheItemInterface");
        }

        $this->data[$index] = $value;

     }

}
