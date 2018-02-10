<?php namespace Comodojo\Cache\Components;

use \Comodojo\Cache\Interfaces\EnhancedCacheItemPoolInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

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

class StackManager extends AbstractStackManager {

    public function add(EnhancedCacheItemPoolInterface $provider, $weight) {

        return $this->genericAdd($provider, $weight);

    }

    public function remove($id) {

        try {

            return parent::remove($id);

        } catch (Exception $e) {

            throw new CacheException($e->getMessage());

        }

    }

    public function get($id) {

        try {

            return parent::get($id);

        } catch (Exception $e) {

            throw new CacheException($e->getMessage());

        }

    }

}
