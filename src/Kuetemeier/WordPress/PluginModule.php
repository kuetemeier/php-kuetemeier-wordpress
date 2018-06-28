<?php

/**
 * Kuetemeier WordPress Plugin - PluginModule
 *
 * @package   kuetemeier-essentials
 * @author    Jörg Kütemeier (https://kuetemeier.de/kontakt)
 * @license   GNU General Public License 3
 * @link      https://kuetemeier.de
 * @copyright 2018 Jörg Kütemeier
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Kuetemeier\WordPress;

// KEEP THIS for security reasons - blocking direct access to the PHP files by checking for the ABSPATH constant.
defined('ABSPATH') || die('No direct call!');


abstract class PluginModule
{

    protected $config;


    abstract public static function manifest();


    public function __construct($config)
    {
        $this->config = $config;
    }


    public function commonInit()
    {
        return; // placeholder
    }


    public function adminInit($options)
    {
        $options->registerAdminOptions($this->getAdminOptionSettings(), $this->manifest());
    }


    public function getAdminOptionSettings()
    {
        return array(); // placeholder
    }


    public function frontendInit()
    {
        return; // placeholder
    }


    /**
     * Shortcut for Modules, so they don't have to hardcode their ID on options requests.
     *
     * @see Config::getOption
     */
    public function getOption($key, $module = '', $default = null)
    {
        if (empty($module)) {
            $module = $this->manifest()['id'];
        }
        return $this->config->getOption($key, $module, $default);
    }


    /**
     * Shortcut for Modules, so they don't have to hardcode their ID on options requests.
     *
     * @see Config::getOption
     */
    public function setOption($key, $value, $module = '')
    {
        if (empty($module)) {
            $module = $this->manifest()['id'];
        }
        return $this->config->getOption($key, $value, $module);
    }
}
