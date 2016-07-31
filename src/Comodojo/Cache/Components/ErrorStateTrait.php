<?php namespace Comodojo\Cache\Components;

/**
 * Set/get/reset error state
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

trait ErrorStateTrait {

    /**
     * Current error state
     *
     * @var bool
     */
    private $error_state = false;

    /**
     * Current error message (if any)
     *
     * @var string
     */
    private $error_message;

    /**
     * {@inheritdoc}
     */
    public function setErrorState($message = null) {

        $this->error_state = true;
        $this->error_message = $message;

        return $this;

    }

    /**
     * {@inheritdoc}
     */
    public function resetErrorState() {

        $this->error_state = false;
        $this->error_message = null;

        return $this;

    }

    /**
     * {@inheritdoc}
     */
    public function getErrorState() {

        return $this->error_state;

    }

    /**
     * {@inheritdoc}
     */
    public function getErrorMessage() {

        return $this->error_message;

    }

}
