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

namespace Kuetemeier\WordPress\Settings\Options;

/*********************************
 * KEEP THIS for security reasons
 * blocking direct access to our plugin PHP files by checking for the ABSPATH constant
 */
defined( 'ABSPATH' ) || die( 'No direct call!' );


class CheckBox extends OptionBase {

/*
		$options = $this->get_wp_plugin()->get_options();

		$value = $this->get();

		// Assemble a compound and escaped id string.
		$esc_id = esc_attr( $this->get_module_id() . '_' . $this->get_id() );

		// Next, we update the name attribute to access this element's ID in the context of the display options array
		// We also access the show_header element of the options collection in the call to the checked() helper function
		$esc_html = '<input type="checkbox" id="' . $esc_id . '" name="' . $this->get_wp_plugin()->get_db_option_table_base_key();
		$esc_html .= '[' . esc_attr( $this->get_module_id() ) . '][' . esc_attr( $this->get_id() ) . ']" value="1" ' . checked( 1, $value, false ) . '/>';

		$esc_html .= $this->display_label_for_html( $esc_id );
		$esc_html .= $this->display_description_html( $esc_id );

		// phpcs:disable WordPress.XSS.EscapeOutput
		// $esc_html contains only escaped content.
		echo $esc_html;
		// phpcs:enable WordPress.XSS.EscapeOutput

*/

    public function defaultDisplay($args)
    {
        $esc_id = $this->settingOption->getID();
        $value = $this->settingOption->getValue();

		$esc_html = '<input type="checkbox" id="' . $esc_id . '" name="' . $this->settingOption->getDBKey();
		$esc_html .= '[' . esc_attr( $this->settingOption->getModule() ) . '][' . esc_attr( $this->settingOption->getID() ) . ']" value="1" ' . checked( 1, $value, false ) . '/>';

        $esc_html .= $this->getHTMLDisplayLabelFor($esc_id);
        $esc_html .= $this->getHTMLDescription($esc_id);

        // phpcs:disable WordPress.XSS.EscapeOutput
		// $esc_html contains only escaped content.
		echo $esc_html;
		// phpcs:enable WordPress.XSS.EscapeOutput
    }

}
