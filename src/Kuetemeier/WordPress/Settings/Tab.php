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

namespace Kuetemeier\WordPress\Settings;

/*********************************
 * KEEP THIS for security reasons
 * blocking direct access to our plugin PHP files by checking for the ABSPATH constant
 */
defined( 'ABSPATH' ) || die( 'No direct call!' );

/**
 * You can register Sections to a Tab.
 */
class Tab extends SettingsBase{

    public function __construct($pageConfig)
    {
        parent::__construct($pageConfig, array('id', 'title', 'page'));

        $this->registerMeOn(SettingsBase::TPAGE);
    }

    public function adminInitFromPage($page)
    {
        $registeredSections = $this->getRegisteredSections();
        foreach($registeredSections->keys() as $key) {
            $section = $registeredSections->get($key);
            $section->adminInitFromTab($page, $this);
        }
    }

    public function register($type, $item)
    {
        if ($type === SettingsBase::RSECTION) {
            parent::register($type, $item);
        } else {
            $this->wp_die_error('You can only register Sections to a Tab. You tried Type: "'.esc_html($type).'" for ID:"'.esc_html($item->getID()).'".');
        }
    }

}
