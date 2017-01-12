<?php namespace Comodojo\Cache\Traits;

use \DateTime;

/**
 *
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

trait StatefulTrait {

    private $id;

    private $state = 0;

    private $state_message;

    private $state_time;

    public function getId() {

        return $this->id;

    }

    public function getState() {

        return $this->state;

    }

    public function getStateMessage() {

        return $this->state_message;

    }

    public function getStateTime() {

        return $this->state_time;

    }

    public function setState($state, $message = null) {

        $this->state = $state === self::CACHE_SUCCESS ? self::CACHE_SUCCESS : self::CACHE_ERROR;

        $this->state_message = $message;

        $this->state_time = new DateTime('now');

        return $this;

    }

}
