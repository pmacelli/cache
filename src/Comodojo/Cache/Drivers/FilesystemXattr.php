<?php namespace Comodojo\Cache\Drivers;

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

class FilesystemXattr extends AbstractDriver {

    const DRIVER_NAME = "filesystem-xattr";

    protected $cache_folder;

    public function __construct(array $configuration) {

        $this->cache_folder = $configuration['cache-folder'];

        if ( $this->test() === false ) throw new Exception("Folder ".$this->cache_folder." not writable");

    }

    public function test() {

        return file_exists($this->cache_folder) && is_writable($this->cache_folder);

    }

    public function get($key, $namespace) {

        if ( $this->has($key, $namespace) ) {

            $cacheFile = $this->cache_folder."$key-$namespace.cache";

            $data = @file_get_contents($cacheFile);

            if ( $data === false ) throw new Exception("Error reading cache content $cacheFile");

            return $data;

        }

        return null;

    }

    public function set($key, $namespace, $value, $ttl = null) {

        $cacheFile = $this->cache_folder."$key-$namespace.cache";

        if ( $ttl == null || $ttl == 0 ) {
            $ttl = 0;
        } else {
            $ttl = time()+intval($ttl);
        }

        $cached = @file_put_contents($cacheFile, $value, LOCK_EX);

        if ( $cached === false ) throw new Exception("Error writing cache object $cacheFile");

        $tagged = @xattr_set($cacheFile, "EXPIRE", $ttl, XATTR_DONTFOLLOW);

        if ( $tagged === false ) throw new Exception("Error writing cache ttl $cacheFile");

        return true;

    }

    public function delete($key, $namespace) {

        $cacheFile = $this->cache_folder."$key-$namespace.cache";

        if ( file_exists($cacheFile) ) {

            $u = unlink($cacheFile);

            return $u;

        }

        return false;

    }

    public function clear($namespace = null) {

        $file_list;

        $result = [];

        if ( $namespace === null ) {

            $file_list = glob($this->cache_folder."*.cache");

        } else {

            $file_list = glob($this->cache_folder."*-$namespace.cache");

        }

        if ( empty($file_list) ) return true;

        foreach ( $file_list as $file ) $result[] = unlink($file);

        return !in_array(false, $result);

    }

    public function getMultiple(array $keys, $namespace) {

        $result = [];

        foreach ($keys as $key) {
            $result[$key] = $this->get($key, $namespace);
        }

        return $result;

    }

    public function setMultiple(array $key_values, $namespace, $ttl = null) {

        $result = [];

        foreach ($key_values as $key => $value) {
            $result[] = $this->set($key, $namespace, $value, $ttl);
        }

        return !in_array(false, $result);

    }

    public function deleteMultiple(array $keys, $namespace) {

        $result = [];

        foreach ($keys as $key) {
            $result[] = $this->delete($key, $namespace);
        }

        return !in_array(false, $result);

    }

    public function has($key, $namespace) {

        $time = time();

        $cacheFile = $this->cache_folder."$key-$namespace.cache";

        if ( file_exists($cacheFile) ) {

            $expire = xattr_get($cacheFile, "EXPIRE", XATTR_DONTFOLLOW);

            if ( $expire === false ) {
                throw new Exception("Error reading cache ttl for $cacheFile");
            } else if ( $expire < $time && $expire != 0 ) {
                $this->delete($key, $namespace);
                return false;
            } else {
                return true;
            }

        }

        return false;

    }

    public function stats() {

        return ['objects' => count(glob($this->cache_folder."*.cache"))];

    }

}
