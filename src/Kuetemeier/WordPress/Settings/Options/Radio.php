<?php

/**
 * Kuetemeier WordPress Plugin - Setting - Option - Radio
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


class Radio extends \Kuetemeier\WordPress\Settings\Option
{


    public function defaultDisplay($args)
    {
        // Get current value.
        $value = $this->getValue();

        // Assemble a compound and escaped id string.
        $escID = esc_attr($this->getID());
        // Assemble an escaped name string. The name attribute is importan, it defines the keys for the $input array in validation.
        $escName = esc_attr($this->getDBKey() . '[' . $this->getModule() . '][' . $this->getID() . ']');

        $class = $this->get('class', '');

        // Compose output.
        $escHtml = '<fieldset>';
        foreach ($this->get('values') as $key => $v) {
            $escHtml .= '<input type="radio" id="' . $escID . '-' . esc_attr($key) . '" name="' . $escName . '" class="' . esc_attr($class) . '"';
            $escHtml .= ' value="' . esc_attr($key) . '"';
            if ($key == $value) {
                $escHtml .= ' checked';
            }
            $escHtml .= '>';

            $escHtml .= '<label for="' . $escID . '-' . esc_attr($key) . '">' . esc_html($v) . '</label> ';
            if ($this->get('lineBreak', false)) {
                $escHtml .= '<br />';
            }
        }
        $escHtml .= '</fieldset>';
        $escHtml .= $this->getHTMLDisplayLabelFor($escID);
        $escHtml .= $this->getHTMLDescription($escID);

		// phpcs:disable WordPress.XSS.EscapeOutput
        // $esc_html contains only escaped content.
        echo $escHtml;
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
        // pre sanitize, just for the case...
        $selected = $this->sanitizeText($input, false);

        // check if selected item is in the list of possible keys
        $values = $this->get('values', array());

        if (isset($values[$selected])) {
            return $selected;
        } else {
            return $this->getEmptyValue();
        }
    }


    public function getEmptyValue()
    {
        return '';
    }
}
