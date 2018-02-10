<?php namespace Comodojo\SimpleCache\Providers;

use \Comodojo\Foundation\Logging\Manager as LogManager;
use \Psr\SimpleCache\CacheInterface;
use \Psr\Log\LoggerInterface;

/**
 * @package     Comodojo Cache
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

abstract class AbstractProvider implements CacheInterface {

    /**
     * Current logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    protected $driver;

    /**
     * Class constructor
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct(LoggerInterface $logger = null) {

        $this->setLogger($logger);

    }

    /**
     * {@inheritdoc}
     */
    public function getLogger() {

        return $this->logger;

    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger = null) {

        $this->logger = is_null($logger) ? LogManager::create('cache', false)->getLogger() : $logger;

        return $this;

    }

    abstract public function get($key, $default = null);

    abstract public function set($key, $value, $ttl = null);

    abstract public function delete($key);

    abstract public function clear();

    abstract public function getMultiple($keys, $default = null);

    abstract public function setMultiple($values, $ttl = null);

    abstract public function deleteMultiple($keys);

    abstract public function has($key);

}
