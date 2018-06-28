<?php

/**
 * Kuetemeier WordPress Plugin - Setting - Section
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


class Section extends SettingsBase
{

    public function __construct($pageConfig)
    {
        parent::__construct($pageConfig, array('id', 'title'));

        $this->registerMeOn(array(SettingsBase::TPAGE, SettingsBase::TTAB));
    }


    public function callbackDisplaySection()
    {
        $this->echoContent();

        $registeredOptions = $this->getRegisteredOptions();
        foreach ($registeredOptions->keys() as $key) {
            $option = $registeredOptions->get($key);
            $option->echoCustomContent();
        }
    }


    /**
     * Returns true if the given `$tab` is in the list of tabs in this options configuration.
     */
    public function isInTab($tab)
    {
        if (empty($this->get('tabs'))) {
            return false;
        }
        return (isset($this->get('tabs') {
            $tab}));
    }


    /**
     * Add this section to the WordPress Settings API if the page and tab matches
     * (or is not detectable, e.g. when submitting to options.php).
     *
     * @see https://codex.wordpress.org/Function_Reference/add_settings_section
     */
    public function adminInitFromPage($page)
    {
        $sectionID = 'k-p-' . $page->get('id') . '-s-' . $this->get('id');

        add_settings_section(
            $sectionID, // id
            $this->get('title'), // title
            array($this, 'callbackDisplaySection'), // display callback
            $page->getID() // page
        );

        $registeredOptions = $this->getRegisteredOptions();
        foreach ($registeredOptions->keys() as $key) {
            $option = $registeredOptions->get($key);
            $option->adminInitFromSection($page, $this, $sectionID, $page->getID());
        }
    }


    public function adminInitFromTab($page, $tab)
    {
        $sectionID = 'k-t-' . $tab->get('id') . '-s-' . $this->get('id');
        $pageID = $page->getID() . '-t-' . $tab->getID();

        add_settings_section(
            $sectionID, // id
            $this->get('title'), // title
            array($this, 'callbackDisplaySection'), // display callback
            $pageID // page
        );

        $registeredOptions = $this->getRegisteredOptions();
        foreach ($registeredOptions->keys() as $key) {
            $option = $registeredOptions->get($key);
            $option->adminInitFromSection($page, $this, $sectionID, $pageID);
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

        return $validInput;
    }
}
