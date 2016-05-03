<?php namespace Comodojo\Cache\Components;

use \Comodojo\Exception\CacheException;

/**
 * Timestamp Trait
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
 
trait TimeTrait {
 
    /**
     * Relative cache time
     *
     * @var int
     */
    protected $current_time = null;
    
    /**
     * {@inheritdoc}
     */
    public function getTime() {
    
        return $this->current_time;
    
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTime($time = null) {
        
        if ( is_null($time) ) $this->current_time = time();

        else if ( preg_match('/^[0-9]{10}$/', $time) ) $this->current_time = $time;
        
        else {
            
            throw new CacheException("Invalid time");
            
        }

        return $this;
        
    }
    
}