<?php

/**
 * Kuetemeier WordPress Plugin - Setting - Option - Text
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


class Text extends \Kuetemeier\WordPress\Settings\Option
{


    public function defaultDisplay($args)
    {
        $this->displayInput('text', 'regular-text ltr code');
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
        return $this->sanitizeText($input, false);
    }


    public function getEmptyValue()
    {
        return '';
    }
}
