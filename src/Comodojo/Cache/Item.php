<?php namespace Comodojo\Cache;

use \Psr\Cache\CacheItemInterface;
use \Comodojo\Cache\Components\KeyValidator;
use \DateTimeInterface;
use \DateTime;
use \DateInterval;
use \Comodojo\Exception\InvalidCacheArgumentException;

/**
 * Item class for everything a poll will return.
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

class Item implements CacheItemInterface {

    protected $key;

    protected $data;

    protected $hit = false;

    protected $expiration = 0;

    public function __construct($key, $hit = false) {

        if ( KeyValidator::validateKey($key) === false ) {
            throw new InvalidCacheArgumentException('Invalid key provided');
        }

        $this->key = $key;

        $this->hit = $hit;

    }

    /**
     * {@inheritdoc}
     */
    public function getKey() {

        return $this->key;

    }

    /**
     * {@inheritdoc}
     */
    public function get() {

        return $this->isHit() ? $this->data : null;

    }

    /**
     * {@inheritdoc}
     */
    public function isHit() {

        // return !is_null($this->data);
        return $this->hit === true;

    }

    /**
     * {@inheritdoc}
     */
    public function set($value) {

        $this->data = $value;

        return $this;

    }

    /**
     * {@inheritdoc}
     */
    public function expiresAt($expiration=null) {

        if (is_null($expiration)) {
            $this->expiration = 0;
        }

        if ( $expiration instanceof DateTimeInterface ) {
            $this->expiration = $expiration;
        }

        return $this;

    }

    /**
     * {@inheritdoc}
     */
    public function expiresAfter($time) {

        if ( is_null($time) ) {
            $this->expiration = 0;
        }

        if ( is_numeric($time) ) {
            $this->expiration = new DateTime('now +' . $time . ' seconds');
        }

        if ( $time instanceof DateInterval ) {
            $expiration = new DateTime('now');
            $expiration->add($time);
            $this->expiration = $expiration;
        }

        return $this;

    }

    /**
     * Returns the raw value, regardless of hit status.
     *
     * Although not part of the CacheItemInterface, this method is used by
     * the pool for extracting information for saving.
     *
     * @return mixed
     *
     * @internal
     */
    public function getRaw() {

        return $this->data;

    }

    /**
     * Get currently (calculated) ttl of cache item
     *
     * This method is not part of the CacheItemInterface.
     *
     * @return int
     *
     * @internal
     */
    public function getTtl() {

        if (is_null($this->expiration)) return null;

        if ($this->expiration === 0) return 0;

        $now = new DateTime("now");

        if ( $now > $this->expiration ) return -1;

        return (int) $now->diff($this->expiration)->format("%r%s");

    }

    /**
     * Get expiration time (absolute)
     *
     * This method is not part of the CacheItemInterface.
     *
     * @return int
     *
     * @internal
     */
    public function getExpiration() {

        return $this->expiration;

    }

    public function __toString() {

        return serialize($this->get());

    }

}
