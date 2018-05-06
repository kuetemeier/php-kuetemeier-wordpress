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


abstract class OptionBase {

    protected $settingOption;

    public function __construct($settingOption)
    {
        $this->settingOption = $settingOption;
    }

    abstract public function defaultDisplay($args);

	/**
	 * Helper function for callbackk__display_setting, returns html for the label.
	 *
	 * @param string $composedID A composed id for the html id fields.
	 *
	 * @return string HTML or '', if label property is empty.
	 *
	 * @since 0.2.1
	 */
    protected function getHTMLDisplayLabelFor($composedID)
    {

        $label = $this->getLabelFor();
		if (empty($label)) {
			return '';
		}

		$escID = esc_attr( $composedID );
		return '<label id="' . $escID . '-label" for="' . $escID . '"> ' . esc_html($label) . '</label>';

    }

    protected function getLabelFor()
    {
        return $this->settingOption->get('label');
    }


	/**
	 * Helper function for callbackk__display_setting, returns html for the description.
	 *
	 * @param string $composed_id A composed id for the html id fields.
	 *
	 * @return string HTML or '', if description property is empty.
	 *
	 * @since 0.2.1
	 */
    protected function getHTMLDescription($composedID)
    {
        $description = $this->getDescription();
		if (empty($description)) {
			return '';
		}

		$escID = esc_attr($composedID);

		return '<p class="description" id="' . $escID . '-description">' . esc_html($description) . '</p>';
	}

    protected function getDescription()
    {
        return $this->settingOption->get('description');
    }

}
