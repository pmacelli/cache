<?php namespace Comodojo\Cache\Components;

use \Comodojo\Foundation\DataAccess\ArrayAccessTrait;
use \Comodojo\Foundation\DataAccess\CountableTrait;
use \Comodojo\Foundation\DataAccess\IteratorTrait;
use \Iterator;
use \ArrayAccess;
use \Countable;

/**
 *
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

class CacheItemPoolManagerStats implements Iterator, ArrayAccess, Countable {

    use CountableTrait;
    use IteratorTrait;
    use ArrayAccessTrait;

    private $data = [];

    public function add(EnhancedCacheItemPoolStats $pool_stats) {

        $this[$pool_stats->getId()] = $pool_stats;

    }

}
