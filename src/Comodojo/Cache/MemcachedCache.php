<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Providers\MemcachedProvider;
use \Psr\Log\LoggerInterface;

/**
 * Memcached cache class
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

class MemcachedCache extends MemcachedProvider {

    /**
     * Class constructor
     *
     * @param   string          $server         Server address (or IP)
     * @param   string          $port           (optional) Server port
     * @param   string          $weight         (optional) Server weight
     * @param   string          $persistent_id  (optional) Persistent id
     * @param   \Monolog\Logger $logger         Logger instance
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct( $server, $port=11211, $weight=0, $persistent_id=null, LoggerInterface $logger=null ) {

        parent::__construct($server, $port, $weight, $persistent_id, $logger);

        $this->logger->notice("Use of MemcachedCache is deprecated, please use MemcachedProvider instead.");

    }

}
