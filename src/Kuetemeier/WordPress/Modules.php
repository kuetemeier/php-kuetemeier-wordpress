<?php
/**
 * Vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4:
 *
 * @package    kuetemeier-essentials
 * @author     Jörg Kütemeier (https://kuetemeier.de/kontakt)
 * @license    GNU General Public License 3
 * @link       https://kuetemeier.de
 * @copyright  2018 Jörg Kütemeier
 *
 *
 * Copyright 2018 Jörg Kütemeier (https://kuetemeier.de/kontakt)
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

/*********************************
 * KEEP THIS for security reasons
 * blocking direct access to our plugin PHP files by checking for the ABSPATH constant
 */
defined( 'ABSPATH' ) || die( 'No direct call!' );


class Modules extends \Kuetemeier\Collection\Collection {

    private $config;

    public function __construct($config) {
        $this->config = $config;
    }

    public function load_sources($modules_list = array()) {
        if (!isset($modules_list) || count($modules_list) === 0) {
            return;
        }

        $namespace = '\\'.$this->config->get('plugin/modules/namespace').'\\';

        foreach($modules_list as $module_id) {
            $srcdir = trailingslashit($this->config->get('plugin/modules/srcdir', trailingslashit($this->config->get('plugin/dir')).'src/module'));

            require_once $srcdir.'class-'.$module_id.'.php';

            $class_name = $namespace.ucfirst($module_id);

            $manifest = $class_name::manifest();

            $this->config->set('default/'.$module_id, $manifest['config'], true);

            $this->set($module_id, $class_name);
        }
    }

    public function init() {

        // get all modules that come with this plugin (default: none - empty array)
        $all_modules = $this->config->get('plugin/modules/list', array());

        // load only activated modules (with fallback to all) if this is a frontend call
        $modules_list = (is_admin()) ? $all_modules : $this->config->get('options/modules/activated', $all_modules);

        $this->load_sources($modules_list);
    }

}
