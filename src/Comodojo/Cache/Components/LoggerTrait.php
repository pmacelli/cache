<?php namespace Comodojo\Cache\Components;

use \Psr\Log\LoggerInterface;

/**
 * Cache provider interface
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

trait LoggerTrait {

    /**
     * Current logger
     *
     * @var LoggerInterface
     */
    protected $logger;

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

        $this->logger = is_null($logger) ? new NullLogger() : $logger;

        return $this;

    }

}
