<?php namespace Comodojo\Cache\Providers;

use \Comodojo\Cache\Components\StatefulCacheItemPoolInterface;
use \Comodojo\Cache\Components\StatefulTrait;
use \Comodojo\Cache\Components\NamespaceTrait;

/**
 * Abstract stateful provider implementation
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

abstract class AbstractStatefulProvider
    extends AbstractProvider
    implements StatefulCacheItemPoolInterface {

    use StatefulTrait;
    use NamespaceTrait;

    public function __construct(LoggerInterface $logger = null) {

        parent::__construct($logger);

        $this->id = self::getUniqueId();

        $this->setState(self::CACHE_SUCCESS);

    }

    abstract public function getStatus();

    abstract public function clearNamespace();

    /**
     * Generate a unique id (64 chars)
     *
     * @return string
     */
    protected static function getUniqueId() {

        return substr(md5(uniqid(rand(), true)), 0, 64);

    }

}
