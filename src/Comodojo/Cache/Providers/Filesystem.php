<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Item;
use \Comodojo\Cache\Traits\BasicCacheItemPoolTrait;
use \Comodojo\Cache\Traits\FilesystemBackendTrait;
use \Comodojo\Cache\Components\EnhancedCacheItemPoolStats;
use \Comodojo\Cache\Components\KeyValidator;
use \Psr\Cache\CacheItemInterface;
use \Comodojo\Exception\CacheException;
use \Comodojo\Exception\InvalidCacheArgumentException;
use \Exception;

/**
 * Store cache on filesystem
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

    use BasicCacheItemPoolTrait;
    use FilesystemBackendTrait;

    /**
     * Current cache folder
     *
     * @var string
     */
    protected $cache_folder;

    /**
     * Flag to handle xattr availability
     *
     * @var string
     */
    protected $xattr_support = false;

    /**
     * Class constructor
     *
     * @throws CacheException
     */
    public function __construct($cache_folder, LoggerInterface $logger = null) {

        if ( empty($cache_folder) || !is_string($cache_folder) ) {

            throw new InvalidCacheArgumentException("Invalid or unspecified cache folder");

        }

        parent::__construct($logger);

        $this->cache_folder = $cache_folder[strlen($cache_folder) - 1] == "/" ? $cache_folder : ($cache_folder."/");

        $this->checkFilesystem();

        $this->xattr_support = self::checkXattrSupport() && self::checkXattrFilesystemSupport($this->cache_folder);

    }

    public function getItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        // $shadowName = $this->cache_folder.md5($name)."-".$this->getNamespace();
        $shadowName = $this->cache_folder.$key."-".$this->getNamespace();

        if ( $this->xattr_support ) {

            $data = $this->getXattr($shadowName);

        } else {

            $data = $this->getGhost($shadowName);

        }

        if ( is_null($data) ) return new Item($key);

        $item = new Item($key, true);

        return $item->set(unserialize($data));

    }

    public function hasItem($key) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        // $shadowName = $this->cache_folder.md5($name)."-".$this->getNamespace();
        $shadowName = $this->cache_folder.$key."-".$this->getNamespace();

        if ( $this->xattr_support ) {

            $data = $this->hasXattr($shadowName);

        } else {

            $data = $this->hasGhost($shadowName);

        }

        return $data;

    }

    public function clear() {

        $result = [];

        $file_list = glob($this->cache_folder."*.{cache,expire}", GLOB_BRACE);

        if ( empty($file_list) ) return false;

        foreach ( $file_list as $file ) $result[] = unlink($file);

        return !in_array(false, $result);

    }

    public function clearNamespace() {

        $result = [];

        $file_list = glob($this->cache_folder."*-".$this->getNamespace().".{cache,expire}", GLOB_BRACE);

        if ( empty($file_list) ) return false;

        foreach ( $file_list as $file ) $result[] = unlink($file);

        return !in_array(false, $result);

    }

    public function deleteItem($key) {

        $result = [];

        $file_list = glob($this->cache_folder.$key."-".$this->getNamespace().".{cache,expire}", GLOB_BRACE);

        if ( empty($file_list) ) return false;

        foreach ( $file_list as $file ) $result[] = unlink($file);

        return !in_array(false, $result);

    }

    public function save(CacheItemInterface $item) {

        $ttl = $item->getTtl();

        // $shadowName = $this->cache_folder.md5($name)."-".$this->getNamespace();
        $shadowName = $this->cache_folder.$item->getKey()."-".$this->getNamespace();
        $shadowData = serialize($item->getRaw());
        $shadowTtl = $ttl === 0 ? 0 : time() + $ttl;

        if ( $this->xattr_support ) {

            $return = $this->setXattr($shadowName, $shadowData, $shadowTtl);

        } else {

            $return = $this->setGhost($shadowName, $shadowData, $shadowTtl);

        }

        return $return;

    }

    public function getStats() {

        return new EnhancedCacheItemPoolStats(
            $this->getId(), 
            'filesystem',
            $this->getState(),
            count(glob($this->cache_folder."*.cache")),
            array(
                "xattr_enabled" => $this->xattr_support
            )
        );

    }

    public function test() {

        return $this->checkFilesystem();

    }

    private function checkFilesystem() {

        if ( self::checkCacheFolder($this->cache_folder) === false ) {

            $message = "Cache folder ".$this->cache_folder." is not writeable, disabling provider ".$this->getId()." administratively";

            $this->logger->error($message);

            $this->setState(self::CACHE_ERROR, $message);

            return false;

        }

        $this->setState(self::CACHE_SUCCESS);

        return true;

    }

}
