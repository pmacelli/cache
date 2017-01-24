<?php namespace Comodojo\SimpleCache\Providers;

use \Comodojo\Cache\Drivers\Memcached as MemcachedDriver;
use \Comodojo\Foundation\Validation\DataValidation;
use \Comodojo\Foundation\Validation\DataFilter;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\SimpleCacheException;
use \Comodojo\Exception\InvalidSimpleCacheArgumentException;
use \Exception;
/**
 * Memcached provider
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

class Memcached extends AbstractEnhancedProvider {

    public function __construct(
        $server = '127.0.0.1',
        $port=11211,
        $weight=0,
        $persistent_id=null,
        LoggerInterface $logger=null
    ) {

        if ( empty($server) ) {
            throw new InvalidSimpleCacheArgumentException("Invalid or unspecified memcached server");
        }

        if ( $persistent_id !== null && DataValidation::validateString($persistent_id) === false ) {
            throw new InvalidSimpleCacheArgumentException("Invalid persistent id");
        }

        $port = DataFilter::filterPort($port, 11211);
        $weight = DataFilter::filterInteger($weight);

        try {

            $this->driver = new MemcachedDriver([
                'persistent_id' => $persistent_id,
                'server' => $server,
                'port' => $port,
                'weight' => $weight
            ]);

            parent::__construct($logger);

        } catch (Exception $e) {

            throw new SimpleCacheException($e->getMessage());

        }

    }

    public function getInstance() {
        return $this->driver->getInstance();
    }

    public function getStats() {

        $info = $this->driver->stats();

        $objects = 0;

        foreach ($info as $key => $value) {

            $objects = max($objects, $value['curr_items']);

        }

        return new EnhancedCacheItemPoolStats(
            $this->getId(),
            $this->driver->getName(),
            $this->getState(),
            $objects,
            $info
        );

    }

}
