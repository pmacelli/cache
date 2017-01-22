<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Drivers\FilesystemXattr as FilesystemXattrDriver;
use \Comodojo\Cache\Drivers\FilesystemGhost as FilesystemGhostDriver;
use \Comodojo\Cache\Item;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
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

class Filesystem extends AbstractEnhancedProvider {

    public function __construct($cache_folder, LoggerInterface $logger = null) {

        try {

            if ( empty($cache_folder) ) {
                throw new InvalidCacheArgumentException("Invalid or unspecified cache folder");
            }

            if ( $cache_folder[strlen($cache_folder)-1] != "/" ) $cache_folder = "$cache_folder/";

            if ( self::isXattrSupported($cache_folder) ) {
                $this->driver = new FilesystemXattrDriver(['cache-folder'=>$cache_folder]);
            } else {
                $this->driver = new FilesystemGhostDriver(['cache-folder'=>$cache_folder]);
            }

            parent::__construct($logger);

        } catch (Exception $e) {

            throw new CacheException($e->getMessage());

        }

    }

    public function getStats() {

        $info = $this->driver->stats();

        return new EnhancedCacheItemPoolStats(
            $this->getId(),
            $this->driver->getName(),
            $this->getState(),
            $info,
            []
        );

    }

    protected static function isXattrSupported($folder) {

        return function_exists("xattr_supported") && xattr_supported($folder);

    }

}
