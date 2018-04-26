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


class Page extends Option {

	public function __construct($pageConfig) {

        parent::__construct($pageConfig, 'Page', array('id', 'title'));

        $this->set('priority', 100, false);
        $this->set('slug', $this->get('id'), false);
        $this->set('menuTitle', $this->get('title'), false);
        $this->set('capability', 'manage_options', false);
    }

    public function callback__admin_menu($config) {
		add_menu_page(
			$this->get('title'), // page title
			$this->get('menuTitle'), // menu title
			$this->get('capability'), // capability
			$this->get('slug'), // menu slug
			$this->get('displayFunction') // function
		);
    }
}
