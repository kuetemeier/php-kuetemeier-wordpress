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

namespace Kuetemeier\WordPress\Option;

/*********************************
 * KEEP THIS for security reasons
 * blocking direct access to our plugin PHP files by checking for the ABSPATH constant
 */
defined( 'ABSPATH' ) || die( 'No direct call!' );


class Section extends Option {

	public function __construct($pageConfig) {

        // shortcut 'subpage' for 'page'
        if (isset($pageConfig['subpage'])) {
            $pageConfig['page'] = $pageConfig['subpage'];
        }
        if (isset($pageConfig['tab'])) {
            $pageConfig['tab'] = '';
        }

        parent::__construct($pageConfig, 'Section', array('id', 'page', 'title'));

        $this->set('priority', 100, false);
        $this->set('capability', 'manage_options', false);


        if (!$this->has('page')) {
            wp_die('ERROR: The Section "'.$this->get('id').'" needs a page or subpage option to register to!');
        }

        $page = $this->get('config')->get('options')->getPage($this->get('page'));

        if (empty($page)) {
            wp_die('ERROR: The Section "'.$this->get('id').'" cannot register to the page "'.$this->get('page').'".');
        }

        $page->registerSection($this);
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

    public function addSettingsSection() {
        add_settings_section(
            $this->get('id'), // id
            $this->get('title'), // title
            array($this, 'callback__displaySection'), // display callback
            $this->get('page') // page
        );
    }
}
