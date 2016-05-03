<?php namespace Comodojo\Cache\Components;

use \Comodojo\Exception\CacheException;

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

trait TtlTrait {
    
    /**
     * Default cache ttl
     *
     * @var int
     */
    public static $default_ttl = 3600;
    
    /**
     * Cache ttl (in seconds)
     *
     * @var int
     */
    protected $ttl;
    
    /**
     * {@inheritdoc}
     */
    final public function getTtl() {
        
        return $this->ttl;
        
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTtl($ttl = null) {
        
        if ( is_null($ttl) ) {
            
            $this->ttl = self::$default_ttl;
            
        } else if ( is_int($ttl) ) {
            
            $this->ttl = $ttl;
            
        } else {

            throw new CacheException("Invalid time to live");
            
        }

        return $this;
        
    }
    
}