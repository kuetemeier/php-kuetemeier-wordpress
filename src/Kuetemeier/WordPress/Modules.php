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


final class Modules extends \Kuetemeier\Collection\PriorityHash {

    /**
     * Reference to the plugin configuration.
     *
     * @see Config
     */
    private $config;


    /**
     * Creates a Modules managing instance.
     *
     * @param Config $config Reference to the plugin configuartion.
     */
    public function __construct($config) {
        $this->config = $config;
    }

    /**
     * Load the php source file of all modules listed in `$modules_list` and adds their
     * primary class name to the `$this->class_list`.
     *
     * The source files (and so the list of classes in `$this->class_list`) are loaded
     * in the order that is determined by the priorty in the config `plugin/moudles/available`
     * hash. NOTICE: So a module MUST be in this list to be loaded.
     *
     * @param string[] $modules_list Array with module IDs to be loaded.
     */
    public function load_sources($modules_list = array()) {
        // prevent a second call of this function
        if (!empty($this->elements)) {
            wp_die("Modules::load_sources: ERROR - Elements of Modules are not empty. Do you try to load_sources twice?");
        }

        // don't do anything if list is empty
        if (!isset($modules_list) || empty($modules_list)) {
            return;
        }

        // get all available modules
        $all_modules = $this->config->get('plugin/modules/available', array());

        if (empty($all_modules)) {
            return;
        }

        // sort $all_modules by priority
        uksort($all_modules, function($a, $b) {
            return (int) $a - (int) $b;
        });

        // prepare module namespace
        $namespace = '\\'.$this->config->get('plugin/modules/namespace', $this->config->get('pro/plugin/modules/namespace')).'\\';

        // create hash for faster lookup
        $modules_list = array_flip($modules_list);
        // iterate over all available modules in priority order
        foreach($all_modules as $module_id => $prio) {
            // and load php source, if it is in the $modules_list
            if (isset($modules_list[$module_id])) {
                $srcdir = trailingslashit($this->config->get('plugin/modules/srcdir', trailingslashit($this->config->get('plugin/dir'), $this->config->get('pro/plugin/dir')).'src/Module'));

                //require_once $srcdir.'class-'.$module_id.'.php';
                require_once $srcdir.$module_id.'.php';

                //$class_name = $namespace.ucfirst($module_id);
                $class_name = $namespace.$module_id;

                $manifest = $class_name::manifest();

                $this->config->set('_default/'.$module_id, $manifest['config'], true);

                $this->set($module_id, $prio, $class_name);
            }
        }
    }

    public function init() {

        // get all modules that come with this plugin (default: none - empty array)
        $all_modules     = array_keys($this->config->get('plugin/modules/available', array()));
        $default_enabled = $this->config->get('plugin/modules/default-enabled', array());
        $always_enabled  = $this->config->get('plugin/modules/always-enabled', array());

        // load only activated modules (with fallback to all) if this is a frontend call
        $modules_list = (is_admin()) ? $all_modules : $this->config->get('options/modules/enabled', $default_enabled);

        $modules_list = array_unique($modules_list + $always_enabled);

        $this->load_sources($modules_list);
    }

    public function init_module_classes()
    {
        $this->map(
            function($moduleClass)
            {
                return new $moduleClass($this->config);
            }
        );
    }

    public function foreach_common_init()
    {
        $this->foreach(
            function($id, $module)
            {
                $module->common_init();
            }
        );
    }

    public function foreach_admin_init($options)
    {
        $this->foreachWithArgs(
            function($id, $module, $options)
            {
                $module->admin_init($options);
            },
            $options
        );
    }

    public function foreach_frontend_init()
    {
        $this->foreach(
            function($id, $module)
            {
                $module->frontend_init();
            }
        );
    }
}
