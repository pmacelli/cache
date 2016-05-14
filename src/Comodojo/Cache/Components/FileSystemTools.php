<?php namespace Comodojo\Cache\Components;

/**
 * A collection of filesystem tools
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

class FileSystemTools {

    /**
     * Check if cache folder is writable
     *
     * @param   string  $folder
     *
     * @return  bool
     */
    public static function checkCacheFolder($folder) {

        return is_writable($folder);

    }

    /**
     * Check xattr (extension) support
     *
     * @return  bool
     */
    public static function checkXattrSupport() {

        return function_exists("xattr_supported");

    }

    /**
     * Check xattr (filesystem) support
     *
     * @return  bool
     */
    public static function checkXattrFilesystemSupport($folder) {

        return xattr_supported($folder);

    }

}
