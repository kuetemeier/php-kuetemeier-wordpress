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

namespace Kuetemeier\WordPress\Option;

/*********************************
 * KEEP THIS for security reasons
 * blocking direct access to our plugin PHP files by checking for the ABSPATH constant
 */
defined( 'ABSPATH' ) || die( 'No direct call!' );


class Option extends \Kuetemeier\Collection\Collection {

    const TSECTION  = 'sections';
    const TTAB      = 'tabs';
    const TSETTINGS = 'settings';

    const REGISTERED_OPTIONS = array(
        self::TSECTION  => 'Section',
        self::TTAB      => 'Tab',
        self::TSETTINGS => 'Setting'
    );

	public function __construct($pageConfig, $required = array()) {

        parent::__construct($pageConfig);

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
            $this->wp_die_error('Unknown type "'.html_esc($type).'". Cannot register "'.html_esc($item.getID()).'".');
        }

        $itemCollection = $this->get($type);
        $itemID = $item->getID();

        if ($itemCollection->has($itemID)) {
            $this->wp_die_error(html_esc(self::REGISTERED_OPTIONS[$type]).' with id "'.html_esc($sectionID).'" is already registered');
        }

        $this->get($type)->set($itemID, $item->getPriority(), $item);
        $item->successfullyRegisteredWith($this);

    }

    public function registerSection($section) {
        $this->register(self::TSECTION, $section);
    }

    public function registerTab($section) {
        $this->register(self::TTAB, $section);
    }

    public function registerSetting($section) {
        $this->register(self::TSETTING, $section);
    }

    /**
     * Called after this element is successfully registered to another Option.
     *
     * @see Option::register()
     */
    public function successfullyRegisteredWith($parent)
    {
        // intentionall left blank
        wp_die($parent->get('id'));
    }
}
