<?php

/**
 * Kuetemeier WordPress Plugin - Setting - Option - TextArea
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


/**
 * A Text area.
 *
 * Special Optins:
 *
 * rows         : (default: 5), Number of rows (height) of the TextArea
 * allowHTML    : (defautl: false), Allow HTML Tags in TextArea
 * code         : (default: false), add "code" class
 * allowScripts : (default: false), allow HTML Tags and the Script Tag
 */
class TextArea extends \Kuetemeier\WordPress\Settings\Option
{


    public function defaultDisplay($args)
    {
        // Get current value.
        $value = $this->getValue();
        $rows = $this->getRows();

        // Assemble a compound and escaped id string.
        $escID = esc_attr($this->getID());
        // Assemble an escaped name string. The name attribute is importan, it defines the keys for the $input array in validation.
        $escName = esc_attr($this->getDBKey() . '[' . $this->getModule() . '][' . $this->getID() . ']');

        if ($this->get('large', false)) {
            $class = 'large-text';
        } else {
            $class = 'regular-text ltr';
        }
        if ($this->get('code', false)) {
            $class .= ' code';
        }

        // Compose output.
        $usesCustomDesign = $this->usesCustomDesign();
        $escHtml = '';
        if ($usesCustomDesign) {
            $title = $this->get('title', '');
            if (!empty($title)) {
                $escHtml .= '<h2>' . esc_html($title) . '</h2>';
            }
            $label = $this->get('label', '');
            if (!empty($label)) {
                $escHtml .= '<p>' . esc_html($label) . '</p>';
            }
        }

        $escHtml .= '<textarea id="' . $escID . '" name="' . $escName . '" class="' . esc_attr($class) . '" rows="' . esc_attr($rows) . '"';

        if ($this->has('cols')) {
            $escHtml .= ' cols="' . esc_attr($this->get('cols')) . '"';
        }

        $escHtml .= '/>' . esc_textarea($value) . '</textarea>';
        if (!$usesCustomDesign) {
            $escHtml .= $this->getHTMLDisplayLabelFor($escID);
        }
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
        return $this->sanitizeText($input, true);
    }


    public function getEmptyValue()
    {
        return '';
    }


    public function getRows()
    {
        return $this->get('rows', 5);
    }
}
