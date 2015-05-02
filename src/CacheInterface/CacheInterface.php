<?php namespace Comodojo\Cache\CacheInterface;

/**
 * Object cache interface
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     GPL-3.0+
 *
 * LICENSE:
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

interface CacheInterface {

    /**
     * Set cache element
     *
     * @param   string  $name    Name for cache element
     * @param   mixed   $data    Data to cache
     * @param   int     $ttl     Time to live
     *
     * @return  Object  $this
     */
    public function set($name, $data, $ttl);

    /**
     * Get cache element
     *
     * @param   string  $name    Name for cache element
     *
     * @return  Object  $this
     */
    public function get($name);

    /**
     * Set scope
     *
     * @param   string  $scope
     *
     * @return  Object  $this
     */
    public function setScope($scope);

    /**
     * Get scope
     *
     * @return  string
     */
    public function getScope();

    /**
     * Flush cache (or entire scope)
     *
     * @param   string  $name    Name for cache element
     *
     * @return  bool
     */
    public function flush($name);

    /**
     * Clean cache objects in any scope
     *
     * @return  bool
     */
    public function purge();

    /**
     * Cache status
     *
     * @return  array
     */
    public function status();

}
