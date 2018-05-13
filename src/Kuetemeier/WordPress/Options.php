<?php

/**
 * Kuetemeier WordPress Plugin - Options
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


final class Options extends \Kuetemeier\Collection\Collection
{

    const OPTIONTYPES = array(
        'pages' => '\Kuetemeier\WordPress\Settings\Page',
        'subpages' => '\Kuetemeier\WordPress\Settings\SubPage',
        'tabs' => '\Kuetemeier\WordPress\Settings\Tab',
        'sections' => '\Kuetemeier\WordPress\Settings\Section',
    );

    const SETTINGSOPTIONTYPES = array(
        'Checkbox' => '\Kuetemeier\WordPress\Settings\Options\CheckBox',
        'TextField' => '\Kuetemeier\WordPress\Settings\Options\TextField'
    );


    private $config;

    private $currentPage = '';


    public function __construct($config)
    {
        $this->config = $config;
        $config->set('_optionsInstance', $this, true);

        foreach (self::OPTIONTYPES as $type => $value) {
            $this->set($type, new \Kuetemeier\Collection\PriorityHash());
        }
        // options are "special" because of their subclasses
        $this->set('options', new \Kuetemeier\Collection\PriorityHash());

        $this->currentPage = (isset($_GET['page']) ? sanitize_key($_GET['page']) : '');

        add_action('admin_init', array(&$this, 'callbackAdminInit'));
        add_action('admin_menu', array(&$this, 'callbackAdminMenu'));
    }


    public function registerAdminOptions($adminOptions, $manifest)
    {

        // iterate over all config options
        foreach (self::OPTIONTYPES as $type => $class) {
            // do we have some config for this type?
            if (isset($adminOptions[$type])) {
                foreach ($adminOptions[$type] as $config) {
                    $config['config'] = $this->config;
                    $config['_optionsInstance'] = $this;

                    // create an object with the matching Option class for every config entry
                    $item = new $class($config);

                    // check, that we do not overwrite an option, that we already have a config for (programming error?)
                    if ($this->get($type)->has($item->get('id'))) {
                        wp_die('ERROR - Options: ' . $class . ' with id "' . $item->get('id') . '" already exists');
                    }
                    // store the new option
                    $this->get($type)->set($item->get('id'), $item->get('priority', 100), $item);
                }
            }
        }

        if (isset($adminOptions['options'])) {
            foreach ($adminOptions['options'] as $config) {
                $config['config'] = $this->config;

                // set option parameter 'module' as a default to the module 'id' (from it's manifest),
                // of the module it is defined in. Better DRY in config definitions.
                if (!isset($config['module'])) {
                    $config['module'] = $manifest['id'];
                }

                $defaults = array(
                    'pro' => false,
                    'alpha' => false,
                    'beta' => false,
                    'label' => '',
                    'description' => ''
                );

                foreach ($defaults as $key => $value) {
                    if (!isset($config[$key])) {
                        $config[$key] = $value;
                    }
                }

                $config['_optionsInstance'] = $this;

                if (isset($config['type'])) {
                    $class = '\Kuetemeier\WordPress\Settings\Options\\' . trim($config['type']);
                } elseif (isset($config['class'])) {
                    $class = $config['class'];
                } else {
                    wp_die('ERROR - Options: no "class" or "type" for option "' . esc_html($config['id']) . '" found.');
                }

                $item = '';
                // create an object with the matching Option class for every config entry
                if (is_string($class)) {
                    $item = new $class($config);
                } else {
                    $item = $class;
                    $item->setConfig($config);
                }

                // check, that we do not overwrite an option, that we already have a config for (programming error?)
                if ($this->get('options')->has($item->get('id'))) {
                    wp_die('ERROR - Options: ' . $class . ' with id "' . $item->get('id') . '" already exists');
                }
                // store the new option
                $this->get('options')->set($item->get('id'), $item->get('priority', 100), $item);
            }
        }
    }


    /**
     * Callback for WP admin_init. Registeres WP settings for the WP Settings API.
     *
     * WARNING: This is a callback. Never call it directly!
     * This method has to be public, so WordPress can see and call it.
     *
     * @return void
     *
     * @since 0.1.0
     */
    public function callbackAdminInit()
    {
        // intentionally left blank
    }


    /**
     * Callback for WP admin_menu. Registeres the admin menu with the WP Settings API.
     *
     * WARNING: This is a callback. Never call it directly!
     * This method has to be public, so WordPress can see and call it.
     *
     * @return void
     *
     * @since 0.1.0
     */
    public function callbackAdminMenu()
    {
        // iterate over all item types
        foreach (self::OPTIONTYPES as $type => $class) {
            $this->get($type)->foreachWithArgs(
                function ($key, $item, $config) {
                    $item->callbackAdminMenu($config);
                },
                $this->config
            );
        }
    }


    public function getPage($id, $noSubPages = false)
    {
        if ($noSubPages) {
            return $this->get('pages')->get($id);
        } else {
            return $this->get('subpages')->get($id, $this->get('pages')->get($id));
        }
    }


    public function getTab($tab, $default = null)
    {
        return $this->get('tabs')->get($tab, $default);
    }


    public function getSection($section, $default = null)
    {
        return $this->get('sections')->get($section, $default);
    }


    public function getCurrentTab()
    {
        if ($this->get('pages')->has($this->getCurrentPage())) {
            return $this->getPage($this->getCurrentPage())->getCurrentTab();
        } else {
            return '';
        }
    }


    public function getCurrentPage()
    {
        return $this->currentPage;
    }


    public function getDBKey()
    {
        return $this->config->get('_plugin/options/key');
    }


    public function getSettingsOptionTypes()
    {
        return self::SETTINGSOPTIONTYPES;
    }


    public function validateOptions($input)
    {
        // if we have no data, do nothing.
        if (empty($input)) {
            //return array();
            return $this->config->getAllOptions();
        }

        // for enhanced security, create a new empty array
        //$validInput = array();
        $validInput = $this->config->getAllOptions();

        $submitID = '';
        $pageID = '';
        $tabID = '';

        // break up submit name for submit-type, page and tab
        foreach (array_keys($input) as $key) {
            if ((substr($key, 0, 7) === 'submit|') || (substr($key, 0, 6) === 'reset|')) {
                $parts = explode('|', $key);
                $count = count($parts);

                if ($count > 0) {
                    $submit = $parts[0];
                    if ($count > 1) {
                        $pageID = $parts[1];
                    }
                    if ($count > 2) {
                        $tabID = $parts[2];
                    }
                    break;
                }
            }
        }

        if (empty($pageID)) {
            return array();
        }

        $page = $this->getPage($pageID);
        $validInput = $page->validateOptions($input, $validInput, $tabID);
        return $validInput; // return validated input
    }
}
