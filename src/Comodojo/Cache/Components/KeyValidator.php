<?php namespace Comodojo\Cache\Components;

use \Comodojo\Foundation\Validation\DataValidation;

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

class KeyValidator {

    /**
     * This method ensure that the key is valid under PSR-6.
     *
     * @param string $key
     *   The key to validate.
     * @return bool
     *   TRUE if the specified key is legal.
     */
    public static function validateKey($key) {

        return DataValidation::validateString($key, function($data) {
            return preg_match('#[{}()/\\\@:]#', $data) === 0;
        });

    }

}
