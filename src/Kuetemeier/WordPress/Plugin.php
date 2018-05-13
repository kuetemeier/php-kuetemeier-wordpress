<?php

/**
 * Kuetemeier WordPress Plugin - Plugin
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


abstract class Plugin
{

    protected $config;


    protected $options;

    /**
     * Initialize the plugin, load frontend modules and prepare backend modules.
     *
     * @param Config  Initial Plugin Config.
     *
     * @since 0.1.0
     */
    public function __construct($config = array())
    {
        $this->config = (is_array($config)) ? new Config($config) : $config;

        if (!$this->config->has('_plugin/id')) {
            wp_die('ERROR Missing "_plugin/id" configuration for Plugin');
        }

        if (!$this->config->has('_plugin/version/this')) {
            wp_die('Missing "_plugin/version/this" configuration for Plugin');
        }

        if (!$this->config->has('_plugin/version/stable')) {
            wp_die('Missing "_plugin/version/stable" configuration for Plugin');
        }

        if (!$this->config->has('_plugin/options/key')) {
            wp_die('Missing "_plugin/options/key" configuration for Plugin');
        }

        $this->config->set('_pluginInstance', $this, true);

        // pro plugin?
        if ($this->isProPlugin()) {
            add_action($this->config->get('_plugin/parent') . '-Plugin-Loaded', array(&$this, 'callbackParentLoaded'));
        } else {
            add_action('plugins_loaded', array(&$this, 'callbackPluginsLoaded'));
        }
    }


    public function callbackParentLoaded($parent_config)
    {
        $this->config->set('_parent', $parent_config, true);

        $modules = new Modules($this->config);

        $modules->init();
        $modules->initModuleClasses();

        $parent_config->set('_pro/modules', $modules, true);
        $parent_config->set('_pro/pluginInstance', $this, true);
    }


    public function callbackPluginsLoaded()
    {
        $this->config->getOptionsFromDB();

        do_action($this->config->get('_plugin/id') . '-Plugin-Loaded', $this->config);

        $this->initModules();
    }


    protected function initModules()
    {
        $modules = new Modules($this->config);

        $modules->init();
        $modules->initModuleClasses();

        $this->config->set('_modules', $modules, true);

        $this->config->init();

        $pro = $this->config->has('_pro');
        $proModules = $this->config->get('_pro/modules');

        $modules->foreachCommonInit();
        if ($pro) {
            $proModules->foreachCommonInit();
        }

        if (is_admin()) {
            $this->options = new Options($this->config);

            $modules->foreachAdminInit($this->options);
            if ($pro) {
                $proModules->foreachAdminInit($this->options);
            }
        } else {
            $modules->foreachFrontendInit();
            if ($pro) {
                $proModules->foreachFrontendInit();
            }
        }
    }


    /**
     * Cloning is forbidden.
     *
     * @return void
     *
     * @since 0.1.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('Don\'t clone me!', 'kuetemeier-essentials'), esc_attr($this->version()));
    }


    /**
     * Unserializing instances of this class is forbidden.
     *
     * @return void
     *
     * @since 0.1.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, esc_html__('No wake up please!', 'kuetemeier-essentials'), esc_attr($this->version()));
    }


    /**
     * Checks if this plugin is based on a known stable version.
     *
     * Hint: this may not be the 'last' stable verstion.
     *
     * @return bool True if it is a stable version, false otherwise.
     *
     * @since 0.1.11
     */
    public function isStableVersion()
    {
        return (version_compare($this->config->get('_plugin/version/this'), $this->config->get('_plugin/version/stable')) === 0);
    }


    public function getVersion()
    {
        return $this->config->get('_plugin/version/this');
    }


    /**
     * Is this instance a Pro Plugin?
     */
    public function isProPlugin()
    {
        return $this->config->get('_plugin/version/pro', false);
    }


    public function proVersionAvailable()
    {
        return $this->config->has('_pro/pluginInstance', false);
    }


    public function getProVersionInstance()
    {
        return $this->config->get('_pro/pluginInstance', false);
    }


    public function getID()
    {
        return $this->config->get('_plugin/id');
    }


    public function getModules()
    {
        return $this->config->get('_modules');
    }
}
