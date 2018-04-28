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

namespace Kuetemeier\WordPress\Settings;

/*********************************
 * KEEP THIS for security reasons
 * blocking direct access to our plugin PHP files by checking for the ABSPATH constant
 */
defined( 'ABSPATH' ) || die( 'No direct call!' );


class Section extends SettingBase {

	public function __construct($pageConfig) {

        // shortcut 'subpage' for 'page'
        if (isset($pageConfig['subpage'])) {
            $pageConfig['page'] = $pageConfig['subpage'];
        }

        // shortcut 'tab' for 'tabs'
        if (isset($pageConfig['tab'])) {
            $pageConfig['tabs'] = $pageConfig['tab'];
        }

        if (!isset($pageConfig['tabs'])) {
            $pageConfig['tabs'] = array();
        }

        // ensure 'tab' is an array
        if (!is_array($pageConfig['tabs'])) {
            $pageConfig['tabs'] = array($pageConfig['tabs']);
        }

        // convert to hash for faster lookups
        if (!empty($pageConfig['tabs'])) {
            $pageConfig['tabs'] = array_flip($pageConfig['tabs']);
        }


        parent::__construct($pageConfig, array('id', 'title'));

        $this->set('priority', 100, false);
        $this->set('capability', 'manage_options', false);


        $options = $this->getPluginOptions();
        if (!$this->has('page')) {
            $tabs = $this->get('tabs');
            if (empty($tabs)) {
                wp_die('ERROR: The Section "'.$this->get('id').'" needs a page, a subpage or a tab option to register to!');
            }

            foreach(array_keys($tabs) as $tabID) {
                $tab = $options->getTab($tabID);

                if(empty($tab)) {
                    $this->wp_die_error('Tab "'.esc_html($tabID).'" could not be found.');
                }
                $tab->registerSection($this);
            }
        } else {

            $page = $options->getPage($this->get('page'));

            if (empty($page)) {
                wp_die('ERROR: The Section "'.$this->get('id').'" cannot register to the page "'.$this->get('page').'".');
            }

            $page->registerSection($this);
        }
    }

    public function callback__admin_menu($config) {
    }

    public function callback__displaySection() {
        $content = $this->get('content');

        if (empty($content)) {
            return;
        }

        if (is_string($content)) {
            esc_html_e($content);
        } elseif (is_callable($content)) {
            $content($this);
        } elseif (is_array($content)) {
            foreach($content as $item) {
                if (is_string($item)) {
                    esc_html_e($item);
                } elseif (is_callable($item)) {
                    $item($this);
                }
            }
        } else {
            echo "ERROR: Section content is not a string, a callable or an array.";
        }
    }

    /**
     * Returns true if the given `$tab` is in the list of tabs in this options configuration.
     */
    public function isInTab($tab) {
        if (empty($this->get('tabs'))) {
            return false;
        }
        return (isset($this->get('tabs'){$tab}));
    }

    /**
     * Add this section to the WordPress Settings API if the page and tab matches
     * (or is not detectable, e.g. when submitting to options.php).
     *
     * @see https://codex.wordpress.org/Function_Reference/add_settings_section
     */
    public function addSettingsSectionToPage($page) {
        // get plugin instance of the options class
        $options = $this->getPluginOptions();

        // detect current page and tab (if possible)
        $currentPage = $options->getCurrentPage();
        $currentTab = $options->getCurrentTab();

        // we have to add our section if we cannot detect the page or the page matches to our config
        if (empty($currentPage) || ($currentPage == $this->get('page'))) {

            if ($this->isInTab($currentTab) || empty($this->get('tabs'))) {
                $page = empty($this->get('tabs')) ? $this->get('page') : $this->get('page').'-'.$currentTab;

                add_settings_section(
                    $this->get('id'), // id
                    $this->get('title'), // title
                    array($this, 'callback__displaySection'), // display callback
                    $page // page
                );
            } else {
                // only add section to settings if one of the former conditions are met.
            }
        }
    }
}
