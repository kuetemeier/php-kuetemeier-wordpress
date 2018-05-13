<?php

/**
 * Kuetemeier WordPress Plugin - Modules
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

/**
 * Kuetemeier WordPress Plugin - Modules
 */
final class Modules extends \Kuetemeier\Collection\PriorityHash
{

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
    public function __construct($config)
    {
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
    private function loadSources($modules_list = array())
    {
        // prevent a second call of this function
        if (!empty($this->elements)) {
            wp_die("Modules::load_sources: ERROR - Elements of Modules are not empty. Do you try to load_sources twice?");
        }

        // don't do anything if list is empty
        if (!isset($modules_list) || empty($modules_list)) {
            return;
        }

        // get all available modules
        $all_modules = $this->config->get('_plugin/modules/available', array());

        if (empty($all_modules)) {
            return;
        }

        // sort $all_modules by priority
        uksort($all_modules, function ($a, $b) {
            return (int)$a - (int)$b;
        });

        // prepare module namespace
        $namespace = '\\' . $this->config->get('_plugin/modules/namespace', $this->config->get('_pro/plugin/modules/namespace')) . '\\';

        // create hash for faster lookup
        $modules_list = array_flip($modules_list);
        // iterate over all available modules in priority order
        foreach ($all_modules as $moduleID => $prio) {
            // and load php source, if it is in the $modules_list
            if (isset($modules_list[$moduleID])) {
                $srcdir = trailingslashit($this->config->get('_plugin/modules/srcdir', trailingslashit($this->config->get('_plugin/dir'), $this->config->get('_pro/plugin/dir')) . 'src/Modules'));

                $ucModuleID = ucfirst($moduleID);
                //require_once $srcdir.'class-'.$module_id.'.php';
                require_once $srcdir . $ucModuleID . '.php';

                //$class_name = $namespace.ucfirst($module_id);
                $class_name = $namespace . $ucModuleID;

                $manifest = $class_name::manifest();

                $this->config->set('_default/' . $moduleID, $manifest['config'], true);

                $this->set($moduleID, $prio, $class_name);
            }
        }
    }


    public function init()
    {
        // get all modules that come with this plugin (default: none - empty array)
        $all_modules = array_keys($this->config->get('_plugin/modules/available', array()));
        $default_enabled = $this->config->get('_plugin/modules/default-enabled', array());
        $always_enabled = $this->config->get('_plugin/modules/always-enabled', array());

        // load only activated modules (with fallback to all) if this is a frontend call
        //$modules_list = (is_admin()) ? $all_modules : $this->config->get('_/options/modules/enabled', $default_enabled);
        $modules_list = $all_modules;

        $modules_list = array_unique($modules_list + $always_enabled);

        $this->loadSources($modules_list);
    }


    public function initModuleClasses()
    {
        $this->map(
            function ($moduleClass) {
                return new $moduleClass($this->config);
            }
        );
    }


    public function foreachCommonInit()
    {
        $this->doForeach(
            function ($id, $module) {
                $module->commonInit();
            }
        );
    }


    public function foreachAdminInit($options)
    {
        $this->foreachWithArgs(
            function ($id, $module, $options) {
                $module->adminInit($options);
            },
            $options
        );
    }


    public function foreachFrontendInit()
    {
        $this->doForeach(
            function ($id, $module) {
                $module->frontendInit();
            }
        );
    }
}
