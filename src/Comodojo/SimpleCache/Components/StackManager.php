<?php namespace Comodojo\SimpleCache\Components;

use \Comodojo\Cache\Components\AbstractStackManager;
use \Comodojo\SimpleCache\Interfaces\EnhancedSimpleCacheInterface;
use \Comodojo\Exception\SimpleCacheException;
use \Exception;

/**
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

class StackManager extends AbstractStackManager {

    public function add(EnhancedSimpleCacheInterface $provider, $weight) {

        parent::genericAdd($provider, $weight);

    }

    public function remove($id) {

        try {

            return parent::remove($id);

        } catch (Exception $e) {

            throw new SimpleCacheException($e->getMessage());

        }

    }

    public function get($id) {

        try {

            return parent::get($id);

        } catch (Exception $e) {

            throw new SimpleCacheException($e->getMessage());

        }

    }

}
