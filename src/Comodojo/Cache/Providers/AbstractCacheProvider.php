<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Components\AbstractManager;

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

abstract class AbstractCacheProvider extends AbstractManager {

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
