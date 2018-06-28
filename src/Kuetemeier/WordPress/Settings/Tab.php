<?php

/**
 * Kuetemeier WordPress Plugin - Setting - Tab
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

namespace Kuetemeier\WordPress\Settings;

// KEEP THIS for security reasons - blocking direct access to the PHP files by checking for the ABSPATH constant.
defined('ABSPATH') || die('No direct call!');

/**
 * You can register Sections to a Tab.
 */
class Tab extends SettingsBase
{

    public function __construct($pageConfig)
    {
        parent::__construct($pageConfig, array('id', 'title', 'page'));

        $this->registerMeOn(SettingsBase::TPAGE);
    }


    public function adminInitFromPage($page)
    {
        $registeredSections = $this->getRegisteredSections();
        foreach ($registeredSections->keys() as $key) {
            $section = $registeredSections->get($key);
            $section->adminInitFromTab($page, $this);
        }
    }


    public function register($type, $item)
    {
        if ($type === SettingsBase::RSECTION) {
            parent::register($type, $item);
        } else {
            $this->wp_die_error('You can only register Sections to a Tab. You tried Type: "' . esc_html($type) . '" for ID:"' . esc_html($item->getID()) . '".');
        }
    }


    public function validateOptions($input, $validInput)
    {
        $registeredOptions = $this->getRegisteredOptions();

        foreach ($registeredOptions->keys() as $key) {
            $option = $registeredOptions->get($key);
            $validInput = $option->validateOptions($input, $validInput);
            $onChange = $option->get('onChange');
            if (isset($onChange) && is_callable($onChange)) {
                $onChange($validInput);
            }
        }

        $registeredSections = $this->getRegisteredSections();

        foreach ($registeredSections->keys() as $key) {
            $section = $registeredSections->get($key);
            $validInput = $section->validateOptions($input, $validInput);
        }

        return $validInput;
    }
}
