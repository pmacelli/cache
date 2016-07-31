<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Components\IdTrait;
use \Comodojo\Cache\Components\NamespaceTrait;
use \Comodojo\Cache\Components\StatusSwitchTrait;
use \Comodojo\Cache\Components\TimeTrait;
use \Comodojo\Cache\Components\TtlTrait;
use \Comodojo\Cache\Components\LoggerTrait;
use \Comodojo\Cache\Components\ErrorStateTrait;
use \Psr\Log\LoggerInterface;
use \Comodojo\Exception\CacheException;

/**
 * Cache controller
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

abstract class AbstractProvider implements ProviderInterface {

    use IdTrait;
    use NamespaceTrait;
    use StatusSwitchTrait;
    use TimeTrait;
    use TtlTrait;
    use LoggerTrait;
    use ErrorStateTrait;

    /**
     * Class constructor
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct(LoggerInterface $logger = null) {

        try {

            $this->setTime();

            $this->setTtl();

            $this->setCacheId();

            $this->setLogger($logger);

        } catch (CacheException $ce) {

            throw $ce;

        }

    }

    public function getType() {

        return get_class($this);

    }

    /**
     * {@inheritdoc}
     */
    abstract public function set($name, $data, $ttl = null);

    /**
     * {@inheritdoc}
     */
    abstract public function get($name);

    /**
     * {@inheritdoc}
     */
    abstract public function delete($name = null);

    /**
     * {@inheritdoc}
     */
    abstract public function flush();

    /**
     * {@inheritdoc}
     */
    abstract public function status();

}
