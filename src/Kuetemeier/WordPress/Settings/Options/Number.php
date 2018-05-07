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


class Number extends \Kuetemeier\WordPress\Settings\Option {


    public function defaultDisplay($args)
    {
        $this->displayInput('number', 'regular-text ltr code');
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
	public function sanitize($input) {
        $value =  $this->sanitizeText($input, false);
        if (is_numeric($value)) {
            return $value;
        } else {
            return $this->getEmptyValue();
        }
    }

    public function getEmptyValue()
    {
        return '';
    }

}
