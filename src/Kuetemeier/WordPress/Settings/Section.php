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


class Section extends SettingsBase {

    public function __construct($pageConfig)
    {
        parent::__construct($pageConfig, array('id', 'title'));

        $this->registerMeOn(array(SettingsBase::TPAGE, SettingsBase::TTAB));
    }


    public function callback__displaySection()
    {
        $content = $this->get('content');

        if (empty($content)) {
            return;
        }

        if (is_string($content)) {
            esc_html_e($content);
        } elseif (is_callable($content)) {
            $content($this);
        } elseif (is_array($content)) {
            foreach($content as $item) {
                if (is_string($item)) {
                    esc_html_e($item);
                } elseif (is_callable($item)) {
                    $item($this);
                }
            }
        } else {
            echo "ERROR: Section content is not a string, a callable or an array.";
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
        return (isset($this->get('tabs'){$tab}));
    }


    /**
     * Add this section to the WordPress Settings API if the page and tab matches
     * (or is not detectable, e.g. when submitting to options.php).
     *
     * @see https://codex.wordpress.org/Function_Reference/add_settings_section
     */
    public function adminInitFromPage($page)
    {
        $sectionID = 'k-p-'.$page->get('id').'-s-'.$this->get('id');

        add_settings_section(
            $sectionID, // id
            $this->get('title'), // title
            array($this, 'callback__displaySection'), // display callback
            $page->getID() // page
        );

        $registeredOptions = $this->getRegisteredOptions();
        foreach($registeredOptions->keys() as $key) {
            $option = $registeredOptions->get($key);
            $option->adminInitFromSection($page, $this, $sectionID);
        }

    }


    public function adminInitFromTab($page, $tab)
    {
        $sectionID = 'k-t-'.$tab->get('id').'-s-'.$this->get('id');
        $pageID = $page->getID().'-t-'.$tab->getID();
        //wp_die($sectionID);
        add_settings_section(
            $sectionID, // id
            $this->get('title'), // title
            array($this, 'callback__displaySection'), // display callback
            $pageID // page
        );
/*
        add_settings_section(
            'ktest', // id
            'KTEST', // title
            array($this, 'callback__displaySection'), // display callback
            $page->getID() // page
        );
*/
        $registeredOptions = $this->getRegisteredOptions();
        foreach($registeredOptions->keys() as $key) {
            $option = $registeredOptions->get($key);
            $option->adminInitFromSection($page, $this, $sectionID, $pageID);
        }

    }
}
