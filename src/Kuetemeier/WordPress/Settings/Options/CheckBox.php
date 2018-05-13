<?php

/**
 * Kuetemeier WordPress Plugin - Setting - Option - CheckBox
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

namespace Kuetemeier\WordPress\Settings\Options;

// KEEP THIS for security reasons - blocking direct access to the PHP files by checking for the ABSPATH constant.
defined('ABSPATH') || die('No direct call!');


class CheckBox extends \Kuetemeier\WordPress\Settings\Option
{

    public function defaultDisplay($args)
    {
        $esc_id = $this->getID();
        $value = $this->getValue();

        $esc_html = '<input type="checkbox" id="' . $esc_id . '" name="' . $this->getDBKey();
        $esc_html .= '[' . esc_attr($this->getModule()) . '][' . esc_attr($this->getID()) . ']" value="1" ' . checked(1, $value, false) . '/>';

        $esc_html .= $this->getHTMLDisplayLabelFor($esc_id);
        $esc_html .= $this->getHTMLDescription($esc_id);

        // phpcs:disable WordPress.XSS.EscapeOutput
        // $esc_html contains only escaped content.
        echo $esc_html;
		// phpcs:enable WordPress.XSS.EscapeOutput
    }


    /**
     * Sanitize the input value for a Checkbox value.
     *
     * Valid values for Checkboxes are 0 and 1
     *
     * @param string $input An input vlalue.
     *
     * @return int A clean and sanitized version or the 'empty' value, if it cannot be sanitized.
     *
     * @since 0.1.12 Does real sanitization.
     */
    public function sanitize($input)
    {

        switch ($input) {
            case 0:
                return 0;
            case 1:
                return 1;
            case '0':
                return 0;
            case '1':
                return 1;
            case true:
                return 1;
            case false:
                return 0;
            default:
                return $this->getEmptyValue();
        }
    }


    public function getEmptyValue()
    {
        return 0;
    }
}
