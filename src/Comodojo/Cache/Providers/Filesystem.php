<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Components\FileSystemTools;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * File cache class
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

class Filesystem extends AbstractFileSystemProvider {

    /**
     * {@inheritdoc}
     */
    public function set($name, $data, $ttl = null) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");

        if ( is_null($data) ) throw new CacheException("Object content cannot be null");

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        try {

            $this->setTtl($ttl);

            $shadowName = $this->cache_folder.md5($name)."-".$this->getNamespace();

            $shadowData = serialize($data);

            $shadowTtl = $this->getTime() + $this->ttl;

            if ( $this->xattr_support ) {

                $return = $this->setXattr($shadowName, $shadowData, $shadowTtl);

            } else {

                $return = $this->setGhost($shadowName, $shadowData, $shadowTtl);

            }

        } catch (CacheException $ce) {

            throw $ce;

        }

        return $return;

    }

    /**
     * {@inheritdoc}
     */
    public function get($name) {

        if ( empty($name) ) throw new CacheException("Name of object cannot be empty");

        if ( $this->isEnabled() === false ) return null;

        $this->resetErrorState();

        $shadowName = $this->cache_folder.md5($name)."-".$this->getNamespace();

        if ( $this->xattr_support ) {

            $return = $this->getXattr($shadowName, $this->getTime());

        } else {

            $return = $this->getGhost($shadowName, $this->getTime());

        }

        return is_null($return) ? $return : unserialize($return);

    }

    /**
     * {@inheritdoc}
     */
    public function delete($name = null) {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        $return = true;

        if ( is_null($name) ) {

            $filesList = glob($this->cache_folder."*-".$this->getNamespace().".{cache,expire}", GLOB_BRACE);

        } else {

            $filesList = glob($this->cache_folder.md5($name)."-".$this->getNamespace().".{cache,expire}", GLOB_BRACE);

        }

        foreach ( $filesList as $file ) {

            if ( unlink($file) === false ) {

                $this->logger->error("Failed to unlink cache file $file, exiting gracefully", pathinfo($file));

                $this->setErrorState("Failed to unlink cache file $file");

                $return = false;

            }

        }

        return $return;

    }

    /**
     * {@inheritdoc}
     */
    public function flush() {

        if ( !$this->isEnabled() ) return false;

        $this->resetErrorState();

        $return = true;

        $filesList = glob($this->cache_folder."*.{cache,expire}", GLOB_BRACE);

        foreach ( $filesList as $file ) {

            if ( unlink($file) === false ) {

                $this->logger->error("Failed to unlink cache file $file, exiting gracefully", pathinfo($file));

                $this->setErrorState("Failed to unlink cache file $file");

                $return = false;

            }

        }

        return $return;

    }

    /**
     * {@inheritdoc}
     */
    public function status() {

        $filesList = glob($this->cache_folder."*.cache");

        if ( FileSystemTools::checkXattrSupport() ) {

            $options = array(
                "xattr_supported"   =>  true,
                "xattr_enabled"     =>  FileSystemTools::checkXattrFilesystemSupport($this->cache_folder)
            );

        } else {

            $options = array(
                "xattr_supported"   =>  false,
                "xattr_enabled"     =>  false
            );

        }

        return array(
            "provider"  => "file",
            "enabled"   => $this->isEnabled(),
            "objects"   => count($filesList),
            "options"   => $options
        );

    }

}
