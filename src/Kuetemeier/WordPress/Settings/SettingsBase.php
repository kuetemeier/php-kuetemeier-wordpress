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


class SettingsBase extends \Kuetemeier\Collection\Collection {

    const TTAB = 'Tab';
    const TSECTION = 'Section';
    const TPAGE = 'Page';
    const TSETTING = 'Setting';

    const RPAGE     = '_registered/pages';
    const RTAB      = '_registered/tabs';
    const RSECTION  = '_registered/sections';
    const RSETTINGS = '_registered/settings';

    const REGISTERED_OPTIONS = array(
        self::RPAGE     => self::TPAGE,
        self::RTAB      => self::TTAB,
        self::RSECTION  => self::TSECTION,
        self::RSETTINGS => self::TSETTING
    );

    const CONFIG_ALIASES = array(
        'subpage' => 'page',
        'tab'     => 'tabs'
    );

    const CONFIG_DEFAULTS = array(
        'priority' => 100,
        'capability' => 'manage_options'
    );


    /**
     *
     * Valid config options:
     *
     * id           : Unique (for the type) ID of this setting.
     * slug         : (Defaults to ID) Page, Menu, ID slug.
     * tabs         : The tab IDs this Setting should register to.
     * page         : The page ID this Setting should register to.
     * subpage      : alias for 'page'
     *
     */
	public function __construct($settingsConfig, $required=array(), $defaults=array()) {

        // replace aliases if originals are not defined
        foreach(self::CONFIG_ALIASES as $alias => $orig) {
            if ((isset($settingsConfig[$alias])) && (!isset($settingsConfig[$orig]))) {
                $settingsConfig[$orig] = $settingsConfig[$alias];
                unset($settingsConfig[$alias]);
            }
        }

        // TODO: add $defaults to loop
        foreach(self::CONFIG_DEFAULTS as $key => $value) {
            if (!isset($settingsConfig[$key])) {
                $settingsConfig[$key] = $value;
            }
        }

        // dynamic alias (no const possible in php): 'slug' = 'id' if not defined
        if (!isset($settingsConfig['slug'])) {
            $settingsConfig['slug'] = $settingsConfig['id'];
        }

        // 'tabs' not empty
        if (!isset($settingsConfig['tabs'])) {
            $settingsConfig['tabs'] = array();
        }

        // ensure 'tab' is an array
        if (!is_array($settingsConfig['tabs'])) {
            $settingsConfig['tabs'] = array($settingsConfig['tabs']);
        }

        // convert to hash for faster lookups
        if (!empty($settingsConfig['tabs'])) {
            $settingsConfig['tabs'] = array_flip($settingsConfig['tabs']);
        }

        parent::__construct($settingsConfig);

        // set dynamic defaults:
        $this->set('slug', $this->get('id'), false);

        array_push($required, 'id');

        foreach($required as $r) {
            if (!($this->has($r))) {
                $this->wp_die_error(' MUST have a "'.$r.'"!');
            }
        }

        if (!$this->has('displayFunction')) {
            $this->set('displayFunction', array(&$this, 'callback__defaultDisplayFunction'));
        }

        foreach(array_keys(self::REGISTERED_OPTIONS) as $roption) {
            $this->set($roption, new \Kuetemeier\Collection\PriorityHash());
        }
	}


	/**
	 * Default display function.
	 *
	 * WARNING: This is a callback. Never call it directly!
	 * This method has to be public, so WordPress can see and call it.
	 *
	 * @param array $args WordPress default args for display functions.
	 *
	 * @return void
	 *
	 * @since 0.2.2
	 */
    public function callback__defaultDisplayFunction($args)
    {
        if ($this->has('content')) {
            ?>
            <div id="<?php echo esc_attr( $this->get('id') ); ?>">
                <?php echo esc_html( $this->get('content', '') ); ?>
            </div>
            <?php
        }
	}


    public function callback__admin_menu($config)
    {
        return; // placeholder
    }

    public function wp_die_error($message, $errorType='ERROR')
    {
        wp_die(esc_html($errorType).' - '.esc_html(get_class($this)).' "'.esc_html($this->get('id', 'UNDEFINED')).'": '.esc_html($message));
    }

    public function getID()
    {
        return $this->get('id');
    }

    public function getPriority()
    {
        return $this->get('priority', 100);
    }

    public function register($type, $item)
    {
        if (!isset(self::REGISTERED_OPTIONS[$type])) {
            $this->wp_die_error('Unknown type "'.esc_html($type).'". Cannot register "'.esc_html($item.getID()).'".');
        }

        $itemCollection = $this->get($type);
        $itemID = $item->getID();

        if ($itemCollection->has($itemID)) {
            $this->wp_die_error(html_esc(self::REGISTERED_OPTIONS[$type]).' with id "'.esc_html($sectionID).'" is already registered');
        }

        $this->get($type)->set($itemID, $item->getPriority(), $item);
        $item->successfullyRegisteredWith($this);

    }

    public function registerSection($section) {
        $this->register(self::RSECTION, $section);
    }

    public function getRegisteredSections()
    {
        return $this->get(self::RSECTION);
    }

    public function registerTab($tab) {
        $this->register(self::RTAB, $tab);
    }

    public function getRegisteredTabs() {
        return $this->get(self::RTAB);
    }

    public function registerSetting($setting) {
        $this->register(self::RSETTING, $setting);
    }

    /**
     * Called after this element is successfully registered to another Option.
     *
     * @see Option::register()
     */
    public function successfullyRegisteredWith($parent)
    {
        // intentionall left blank
    }

    public function getPlugin()
    {
        return $this->get('config')->get('_/plugin');
    }

    public function getPluginOptions()
    {
        return $this->get('config')->get('_/options');
    }

    public function getPluginID()
    {
        return $this->get('config')->getPlugin()->getID();
    }

    public function getDBKey()
    {
        return $this->getPluginOptions()->getDBKey();
    }

    public function getTabs()
    {
        $tabs = $this->get('tabs');
        if (empty($tabs)) {
            // Return an empty array, even if tabs are not set.
            return array();
        }
        return array_keys($tabs);
    }


    public function registerMeOn($optionTypes)
    {
        if (empty($optionTypes)) {
            return;
        }

        if (!is_array($optionTypes)) {
            $optionTypes = array($optionTypes);
        }

        $registerSuccess = false;
        $options = $this->getPluginOptions();

        foreach ($optionTypes as $optionType) {
            switch ($optionType) {
                case self::TPAGE:
                    $page = $this->get('page');
                    if (empty($page)) {
                        break;
                    }
                    $pageObject = $options->getPage($page);
                    if (!isset($pageObject)) {
                        $this->wp_die_error('registerMeOn - Page "'.esc_html($page).'" is not defined.');
                    } else {
                        switch (get_class($this)) {
                            case 'Kuetemeier\WordPress\Settings\Tab':
                                $pageObject->registerTab($this);
                                $registerSuccess = true;
                                break;
                            case 'Kuetemeier\WordPress\Settings\Section':
                                $pageObject->registerSection($this);
                                $registerSuccess = true;
                                break;
                        }
                    }
                    break;
                case self::TTAB:
                    $tabs = $this->getTabs();

                    foreach ($tabs as $tab) {
                        $tabObject = $options->getTab($tab);
                        if (!isset($tabObject)) {
                            $this->wp_die_error('registerMeOn - Tab "'.esc_html($tab).'" is not defined.');
                        } else {
                            switch (get_class($this)) {
                                case 'Kuetemeier\WordPress\Settings\Section':
                                    $tabObject->registerSection($this);
                                    $registerSuccess = true;
                                    break;
                            }
                        }
                    }
                    break;
                default:
                    $this->wp_die_error('registerMeOn - unknown optionType "'.esc_html($optionType).'"');
            }
        }
        if (!$registerSuccess) {
            $this->wp_die_error('registerMeOn - Could not register "'.esc_html($this->getID()).'" on "'.esc_html(join(', ', $optionTypes)).'"');
        }
        return $registerSuccess;
    }
}
