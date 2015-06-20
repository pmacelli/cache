<?php namespace Comodojo\Cache\CacheTrait;

/**
 * A common cache trait
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

trait CacheTrait {

	/**
     * Determine the current cache scope (default: GLOBAL)
     *
     * @var string
     */
    protected $namespace = "GLOBAL";

    /**
     * current time (in msec)
     *
     * @var float
     */
    protected $current_time = null;
    
    /**
     * Current instance of \Monolog\Logger
     *
     * @var \Monolog\Logger
     */
    protected $logger = null;
    
    /**
     * Cache ttl
     *
     * @var int
     */
    protected $ttl = null;

    /**
     * Get current time
     *
     * @return float
     */
    final public function getTime() {
        
        return $this->current_time;
        
    }

    /**
     * Get current ttl
     *
     * @return int
     */
    final public function getTtl() {
        
        return $this->ttl;
        
    }

    /**
     * Get current namespace
     *
     * @return int
     */
    final public function getNamespace() {

        return $this->namespace;

    }

    /**
     * Get current logger
     *
     * @return \Monolog\Logger
     */
    final public function getLogger() {
        
        return $this->logger;
        
    }

    public function raiseError($message, $parameters=array()) {

        if ( $this->logger instanceof \Monolog\Logger ) $this->logger->addError($message, $parameters);

    }

}