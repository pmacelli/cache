<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Providers\PhpRedisProvider;
use \Psr\Log\LoggerInterface;

/**
 * Redis cache class using PhpRedis extension
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

class PhpRedisCache extends PhpRedisProvider {

    /**
     * Class constructor
     *
     * @param   string          $server         Server address (or IP)
     * @param   integer         $port           (optional) Server port
     * @param   integer         $timeout        (optional) Timeout
     * @param   LoggerInterface $logger         Logger instance
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct( $server, $port=6379, $timeout=0, LoggerInterface $logger=null ) {

        parent::__construct($server, $port, $timeout, $logger);

        $this->logger->notice("Use of PhpRedisCache is deprecated, please use PhpRedisProvider instead.");

    }

}
