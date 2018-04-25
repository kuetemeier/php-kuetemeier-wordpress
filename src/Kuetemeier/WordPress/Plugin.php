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


abstract class Plugin {

	private $config;
	private $options;


	/**
	 * Initialize the plugin, load frontend modules and prepare backend modules.
	 *
	 * @param Config  Initial Plugin Config.
	 *
	 * @since 0.1.0
	 */
	public function __construct( $config = array() ) {
		$this->config = ( is_array($config) ) ? new Config($config) : $config;

		if (!$this->config()->has('version/this')) {
			wp_die('Missing "version" configuration for Plugin');
		}

		if (!$this->config()->has('version/stable')) {
			wp_die('Missing "version_stable" configuration for Plugin');
		}

		if (!$this->config()->has('options/key')) {
			wp_die('Missing "options/key" configuration for Plugin');
		}

		$this->config()->set('plugin', $this, true);

		$this->options = new Options($this->config());
	}


	public function config() {
		return $this->config;
	}

	public function options() {
		return $this->options;
	}

	/**
	 * Cloning is forbidden.
	 *
	 * @return void
	 *
	 * @since 0.1.0
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'Don\'t clone me!', 'kuetemeier-essentials' ), esc_attr( $this->version() ) );
	}


	/**
	 * Unserializing instances of this class is forbidden.
	 *
	 * @return void
	 *
	 * @since 0.1.0
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, esc_html__( 'No wake up please!', 'kuetemeier-essentials' ), esc_attr( $this->version() ) );
	}


	/**
	 * Checks if this plugin is based on a known stable version.
	 *
	 * Hint: this may not be the 'last' stable verstion.
	 *
	 * @return  bool True if it is a stable version, false otherwise.
	 *
	 * @since 0.1.11
	 */
	public function is_stable_version() {
		return ( version_compare( $this->config()->get('version/this'), $this->config()->get('version/stable') ) === 0 );
	}

	public function get_version() {
		return $this->config()->get('version/this');
	}

}
