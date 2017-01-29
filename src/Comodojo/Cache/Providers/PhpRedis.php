<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Drivers\PhpRedis as PhpRedisDriver;
use \Comodojo\Cache\Item;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Comodojo\Foundation\Validation\DataValidation;
use \Comodojo\Foundation\Validation\DataFilter;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
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

class PhpRedis extends AbstractEnhancedProvider {

    public function __construct(
        $server = '127.0.0.1',
        $port = 6379,
        $timeout = 0,
        LoggerInterface $logger = null
    ) {

        if ( empty($server) ) {
            throw new InvalidCacheArgumentException("Invalid or unspecified memcached server");
        }

        $port = DataFilter::filterPort($port, 6379);
        $timeout = DataFilter::filterInteger($timeout, 0, PHP_INT_MAX, 0);

        try {

            $this->driver = new PhpRedisDriver([
                'server' => $server,
                'port' => $port,
                'timeout' => $timeout
            ]);

            parent::__construct($logger);

        } catch (Exception $e) {

            throw new CacheException($e->getMessage());

        }

    }

    public function getInstance() {
        return $this->driver->getInstance();
    }

    public function getStats() {

        $info = $this->driver->stats();

        return new EnhancedCacheItemPoolStats(
            $this->getId(),
            $this->driver->getName(),
            $this->getState(),
            $info['objects'],
            $info['stats']
        );

    }

}
