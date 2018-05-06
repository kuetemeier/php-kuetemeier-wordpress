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


class Option extends SettingsBase
{
    private $base;

	public function __construct($optionConfig) {
        parent::__construct($optionConfig);

        $this->registerMeOn(array(SettingsBase::TPAGE, SettingsBase::TTAB, SettingsBase::TSECTION));

        $type = $this->get('type');
        $settingsOptionTypes = $this->getPluginOptions()->getSettingsOptionTypes();
        if (isset($settingsOptionTypes[$type])) {
            $class = $settingsOptionTypes[$type];
            $this->base = new $class($this);
        } else {
            $this->wp_die_error('Unknown option type: '.esc_html($type));
        }
    }

    public function adminInitFromSection($page, $section, $sectionID, $pageID)
    {
        add_settings_field(
            $sectionID.'-o-'.$this->getID(), // id
            $this->getTitle(), // title
            array(&$this, 'defaultDisplay'), // display function
            $pageID,
            $sectionID
        );
    }

    public function defaultDisplay($args) {
        $this->base->defaultDisplay($args);
    }

    public function getDefault() {
        return $this->get('config')->getDefault($this->getID(), $this->getModule());
    }
}
