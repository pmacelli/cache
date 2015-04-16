<?php namespace Comodojo\Cache\CacheInterface;

/**
 * Cache object interface
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
     * Inject logger
     *
     * @param   \Monolog\Logger  $logger    An instance of Monolog
     */
    public function logger(\Monolog\Logger $logger);

    /**
     * Push object to cache
     *
     * @param   string  $ObjectName    Object's name
     * @param   mixed   $ObjectData    Data (will be serialized)
     * @param   string  $ObjectScope   (optional) a scope for grouping objects
     */
    public function set($ObjectName, $ObjectData, $ObjectScope);

    /**
     * Pull object from cache
     *
     * @param   string  $ObjectName    Object's name
     * @param   string  $ObjectScope   (optional) a scope for grouping objects
     */
    public function get($ObjectName, $ObjectScope);

    /**
     * Check if object is cached
     *
     * @param   string  $ObjectName    Object's name
     * @param   string  $ObjectScope   (optional) a scope for grouping objects
     */
    public function exists($ObjectName, $ObjectScope);

    /**
     * Purge cache selectively
     *
     * @param   string  $ObjectName    Object's name
     * @param   string  $ObjectScope   (optional) a scope for grouping objects
     */
    public function clean($ObjectName, $ObjectScope);

    /**
     * Purge single scope
     *
     * @param   string  $ObjectScope   A scope for grouping objects
     */
    public function cleanScope($ObjectScope);

    /**
     * Purge the whole cache
     *
     */
    public function purge();

    /**
     * Get current cache status (total, active, expired)
     *
     */
    public function status();
