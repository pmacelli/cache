<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Components\AbstractManager;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;
use \Exception;

/**
 * Cache manager
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

class Cache extends AbstractManager {

    public function set($name, $data, $ttl = null) {

        $set = array();

        try {

            foreach ( $this->caches as $cache_id => $cache ) {

                $set[$cache_id] = $cache->set($name, $data, $ttl);

            }

        } catch (CacheException $ce) {

            throw $ce;

        }

        return !in_array(false, $set);

    }

    public function get($name) {

        reset($this->caches);

        $this->selected_cache = null;

        $result = null;

        try {

            switch ( $this->selector ) {

                case 1:

                    $result = $this->getCacheByLoop($this->caches, $name);

                    break;

                case 2:

                    $result = $this->getCacheByLoop(array_reverse($this->caches, true), $name);

                    break;

                case 3:

                    $result = $this->getRandomCache($this->caches, $name);

                    break;

                case 4:

                    $result = $this->getCacheByWeight($this->caches, $this->cache_weights, $name);

                    break;

                case 5:

                    $values = array();

                    foreach ( $this->caches as $cache ) {

                        $values[] = $cache->get($name);

                    }

                    if ( count(array_unique($values)) === 1 ) {

                        $result = $values[0];

                    } else {

                        $this->logger->error("Inconsistent values in providers, exiting gracefully");

                        $result = null;

                    }

                    break;

            }

        } catch (Exception $e) {

            throw $ce;

        }

        return $result;

    }

    public function delete($name = null) {

        $delete = array();

        try {

            foreach ( $this->caches as $cache_id => $cache ) {

                $delete[$cache_id] = $cache->delete($name);

            }

        } catch (CacheException $ce) {

            throw $ce;

        }

        return !in_array(false, $delete);

    }

    public function flush() {

        $flush = array();

        try {

            foreach ( $this->caches as $cache_id => $cache ) {

                $flush[$cache_id] = $cache->flush();

            }

        } catch (CacheException $ce) {

            throw $ce;

        }

        return !in_array(false, $flush);

    }

    public function status() {

        $status = array();

        try {

            foreach ( $this->caches as $cache_id => $cache ) {

                $status[$cache_id] = $cache->status();

            }

        } catch (CacheException $ce) {

            throw $ce;

        }

        return $status;

    }

    private function getCacheByLoop($caches, $name) {

        $result = null;

        $active_cache = false;

        foreach ( $caches as $cache ) {

            if ( $cache->isEnabled() ) {

                $result = $cache->get($name);

                if ( $cache->getErrorState() === false ) {

                    $this->selected_cache = $cache->getCacheId();

                    $active_cache = true;

                    break;

                }

            }

        }

        if ( $active_cache === false ) {
            $this->logger->notice("Cannot find an active provider, exiting gracefully");
        }

        return $result;

    }

    private function getRandomCache($caches, $name) {

        $result = null;

        $active_cache = false;

        $size = sizeof($caches);

        for ( $i = 0; $i < $size; $i++ ) {

            $cache_id = array_rand($caches);

            $cache = $caches[$cache_id];

            if ( $cache->isEnabled() ) {

                $result = $cache->get($name);

                if ( $cache->getErrorState() === false ) {

                    $this->selected_cache = $cache_id;

                    $active_cache = true;

                    break;

                } else {

                    unset($caches[$cache_id]);

                }

            } else {

                unset($caches[$cache_id]);

            }

        }

        if ( $active_cache === false ) {
            $this->logger->notice("Cannot find an active provider, exiting gracefully");
        }

        return $result;

    }

    private function getCacheByWeight($caches, $weights, $name) {

        $result = null;

        $active_cache = false;

        $size = sizeof($weights);

        for ( $i = 0; $i < $size; $i++ ) {

            $cache_ids = array_keys($weights, max($weights));

            $cache_id = $cache_ids[0];

            $cache = $caches[$cache_id];

            if ( $cache->isEnabled() ) {

                $result = $cache->get($name);

                if ( $cache->getErrorState() === false ) {

                    $this->selected_cache = $cache_id;

                    $active_cache = true;

                    break;

                } else {

                    unset($weights[$cache_id]);

                }

            } else {

                unset($weights[$cache_id]);

            }

        }

        if ( $active_cache === false ) {
            $this->logger->notice("Cannot find an active provider, exiting gracefully");
        }

        return $result;

    }

}
