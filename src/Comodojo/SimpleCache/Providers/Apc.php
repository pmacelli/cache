<?php namespace Comodojo\SimpleCache\Providers;

use \Comodojo\Cache\Drivers\Apc as ApcDriver;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\SimpleCacheException;
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

class Apc extends AbstractEnhancedProvider {

    public function __construct(array $properties = [], LoggerInterface $logger = null) {

        try {

            parent::__construct($properties, $logger);

            $this->driver = new ApcDriver();

            $this->test();

        } catch (Exception $e) {

            throw new SimpleCacheException($e->getMessage());

        }

    }

    public function getStats() {

        $info = $this->driver->stats();

        $entries = isset($info['num_entries']) ? $info['num_entries'] : null;

        return new EnhancedCacheItemPoolStats(
            $this->getId(),
            $this->driver->getName(),
            $this->getState(),
            $entries,
            $info
        );

    }

}
