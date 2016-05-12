<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Providers\XCacheProvider;
use \Psr\Log\LoggerInterface;

/**
 * XCache cache class
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

class CacheManager extends Cache {

    /**
     * Class constructor
     *
     * @param LoggerInterface $logger
     *
     * @throws \Comodojo\Exception\CacheException
     */
     public function __construct($select_mode = null, LoggerInterface $logger = null) {

        parent::__construct($select_mode, $logger);

        $this->logger->notice("Use of CacheManager is deprecated, please use Cache class instead.");

    }

}
