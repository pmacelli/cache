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

class PhpRedis extends AbstractEnhancedProvider {

    protected $default_properties = [
        "server" => '127.0.0.1',
        "port" => 6379,
        "timeout" => 0,
        "password" => null
    ];

    public function __construct(array $properties = [], LoggerInterface $logger = null) {

        parent::__construct($properties, $logger);

        $properties = $this->getProperties();

        if ( empty($properties->server) ) {
            throw new InvalidCacheArgumentException("Invalid or unspecified memcached server");
        }

        $port = DataFilter::filterPort($properties->port, 6379);
        $timeout = DataFilter::filterInteger($properties->timeout, 0, PHP_INT_MAX, 0);

        try {

            $this->driver = new PhpRedisDriver([
                'server' => $properties->server,
                'port' => $port,
                'timeout' => $timeout,
                'password' => $properties->password
            ]);

            $this->test();

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
