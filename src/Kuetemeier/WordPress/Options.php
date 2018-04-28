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


final class Options extends \Kuetemeier\Collection\Collection {

    const OPTIONTYPES = array(
        'pages' => '\Kuetemeier\WordPress\Option\Page',
        'subpages' => '\Kuetemeier\WordPress\Option\SubPage',
        'tabs' => '\Kuetemeier\WordPress\Option\Tab',
        'sections' => '\Kuetemeier\WordPress\Option\Section',
        'setting' => '\Kuetemeier\WordPress\Option\Setting'
    );

    private $config;

    private $currentPage = '';

	public function __construct($config) {
        $this->config = $config;
        $config->set('options', $this, true);

        foreach(self::OPTIONTYPES as $type => $value) {
            $this->set($type, new \Kuetemeier\Collection\PriorityHash());
        }

        $this->currentPage = ( isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '' );

		add_action( 'admin_init', array( &$this, 'callback__admin_init' ) );
		add_action( 'admin_menu', array( &$this, 'callback__admin_menu' ) );
    }

    public function registerAdminOptions($adminOptions) {

        // iterate over all config options
        foreach(self::OPTIONTYPES as $type => $class) {
            // do we have some config for this type?
            if (isset($adminOptions[$type])) {
                foreach($adminOptions[$type] as $config) {
                    $config['config'] = $this->config;
                    $config['options'] = $this;
                    // create an object with the matching Option class for every config entry
                    $item = new $class($config);

                    // check, that we do not overwrite an option, that we already have a config for (programming error?)
                    if ($this->get($type)->has($item->get('id'))) {
                        wp_die('ERROR - Options: '.$class.' with id "'.$page->get('id').'" already exists');
                    }
                    // store the new option
                    $this->get($type)->set($item->get('id'), $item->get('priority', 100), $item);
                }
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
	public function callback__admin_init() {

//		foreach ( $this->admin_subpages as $subpage ) {
//			register_setting( $subpage['slug'], $this->get_wp_plugin()->get_db_option_table_base_key(), $subpage['callback__validate_options'] );
//		}

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
	public function callback__admin_menu() {

        // iterate over all item types
        foreach(self::OPTIONTYPES as $type => $class) {
            $this->get($type)->foreachWithArgs(
                function($key, $item, $config){
                    $item->callback__admin_menu($config);
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


    public function getCurrentTab() {
        if ($this->get('pages')->has($this->getCurrentPage())) {
            return $this->getPage($this->getCurrentPage())->getCurrentTab();
        } else {
            return '';
        }
    }


    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function getDBKey() {
        return $this->config->get('plugin/options/key');
    }
}
