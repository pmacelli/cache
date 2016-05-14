<?php namespace Comodojo\Cache\Components;

/**
 * Unique Id Trait
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

trait IdTrait {

    /**
     * A unique id for chache provider
     *
     * @var string
     */
    protected $id = null;

    /**
     * {@inheritdoc}
     */
    public function getCacheId() {

        return $this->id;

    }

    /**
     * Set a unique id (64 chars)
     *
     */
    protected function setCacheId() {

        $this->id = self::getUniqueId();

    }

    /**
     * Generate a unique id (64 chars)
     *
     * @return string
     */
    protected static function getUniqueId() {

        return substr(md5(uniqid(rand(), true)), 0, 64);

    }

}
