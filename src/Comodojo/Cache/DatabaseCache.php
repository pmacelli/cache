<?php namespace Comodojo\Cache;

use \Comodojo\Cache\Providers\Database;
use \Comodojo\Database\EnhancedDatabase;
use \Psr\Log\LoggerInterface;

/**
 * Database cache class
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

class DatabaseCache extends Database {

    /**
     * Class constructor
     *
     * @param   EnhancedDatabase $dbh
     * @param   string           $table          Name of table
     * @param   string           $table_prefix   Prefix for table
     * @param   LoggerInterface  $logger         Logger instance
     *
     * @throws \Comodojo\Exception\CacheException
     */
    public function __construct(EnhancedDatabase $dbh, $table, $table_prefix = null, LoggerInterface $logger = null) {

        parent::__construct($dbh, $table, $table_prefix, $logger);

        $this->logger->notice("Use of DatabaseCache is deprecated, please use \Comodojo\Cache\Providers\Database instead.");


    }

}
