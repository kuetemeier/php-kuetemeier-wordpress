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


class Page extends Option {

    private $replaceBySubPage = null;

	public function __construct($pageConfig, $type = 'Page', $required = array('id', 'title')) {

        parent::__construct($pageConfig, $type, $required);

        $this->set('priority', 100, false);
        $this->set('slug', $this->get('id'), false);
        $this->set('menuTitle', $this->get('title'), false);
        $this->set('capability', 'manage_options', false);

        $this->set('tabs', new \Kuetemeier\Collection\PriorityHash());
        $this->set('sections', new \Kuetemeier\Collection\PriorityHash());

        $slug = $this->get('slug', $this->get('id'));
        $dbKey = $this->getDBKey();

        $currentPage = $this->get('options')->getCurrentPage();
        //if ($slug === $currentPage) {
            //wp_die('Slug: '.$slug.' DB: '.$dbKey);
            //register_setting($slug, $dbKey, array(&$this, 'validateOptions'));
        //}

        add_action('admin_init', array(&$this, 'callback__admin_init'));

    }

    public function callback__admin_init() {
        $slug = $this->get('slug', $this->get('id'));
        $dbKey = $this->getDBKey();

        $this->get('sections')->foreach(
            function($key, $section) {
                $section->addSettingsSection();
            }
        );
/*
        add_settings_section(
            "test-setting", // id
            "Member Only Categories: ", // title
            array($this, 'section_callback'), // display callback
            "optimization" // page
        );
        add_settings_field(
            'kuetemeier-essentials-test-id', // id
            'Categories: ', // title
            array( $this, 'field_callback' ), // display callback
            'optimization', // page
            'test-setting' // section
            // args
        );
*/
        register_setting($slug, $dbKey, array(&$this, 'validateOptions'));
    }

    public function callback__admin_menu($config) {
		add_menu_page(
			$this->get('title'), // page title
			$this->get('menuTitle'), // menu title
			$this->get('capability'), // capability
			$this->get('slug'), // menu slug
			$this->get('displayFunction') // function
        );
    }

    public function registerTab($tab) {
        $this->get('tabs')->set($tab->get('id'), $tab->get('priority'), $tab);
    }

    public function registerSection($section) {
        $this->get('sections')->set($section->get('id'), $section->get('priority'), $section);
    }

    public function displayTabs($currentTab) {
        $tabs = $this->get('tabs');
        $keys = $tabs->keys();

		if (count($keys) > 0 ) {

			echo '<br /></div>';
            echo '<h2 class="nav-tab-wrapper">';

            $slug = $this->get('slug');

			foreach ($keys as $key) {
                $tab = $tabs->get($key);
                $title = $tab->get('title');
				if ( $key === $currentTab ) {
					echo '<a class="nav-tab nav-tab-active" href="?page=' . esc_attr( $slug ) . '&tab=' . esc_attr( $key ) . '">' . esc_html( $title ) . '</a>';
				} else {
					echo '<a class="nav-tab" href="?page=' . esc_attr( $slug ) . '&tab=' . esc_attr( $key ) . '">' . esc_html( $title ) . '</a>';
				}
			}

            echo '</h2>';

            //$this->displaySections($currentTab);
		}

    }

    public function displaySections($forTab = '') {
        do_settings_sections( $page );
        $sections = $this->get('sections');
        $keys = $sections->keys();

        foreach ($keys as $key) {
            $section = $sections->get($key);

            if ($forTab === $section->get('tab', '')) {
                $section->displaySection($this->get('config'));
            }
        }

    }

    public function replaceBySubPage($subPage) {
        $this->replaceBySubPage = $subPage;
    }

    public function callback__defaultDisplayFunction($args)
    {
        if (empty($this->replaceBySubPage)) {
            $page = $this->get('slug');

            $currentTab = $this->get('config')->get('options')->getCurrentTab();
            $tabs = $this->get('tabs');
            if (empty($currentTab) || !$tabs->has($currentTab)) {
                if ($tabs->count() > 0) {
                    $currentTab = $this->get('tabs')->keys()[0];
                } else {
                    $currentTab = '';
                }
            }

            $tab = $currentTab;

            //register_setting($this->get('slug'), 'kuetemeier-essentials', array( &$this, 'sanitizeSettings'));
            //register_setting('kuetemeier-essentials', 'kuetemeier-essentials', array( &$this, 'sanitizeSettings'));

            //register_setting("kuetemeier", "categories");





            ?>
            <div class="wrap">

                <h2><?php echo esc_html( $this->get('title') ); ?></h2>

                <?php
                    if ($this->has('content')) {
                        ?>
                        <div id="<?php echo esc_attr( $this->get('id') ); ?>">
                            <?php echo esc_html( $this->get('content', '') ); ?>
                        </div>
                        <?php
                    }
                    //$this->displaySections();
                    $this->displayTabs($currentTab);
                ?>
                <?php settings_errors(); ?>

                <form method="post" action="options.php">
                    <?php
                    settings_fields( $page );
                    do_settings_sections( $page );
                    $dbKey = $this->getDBKey();
                    $saveButtonText = $this->get('config')->get('plugin/options/saveButtonText', 'Save');
                    $resetButtonText = $this->get('config')->get('plugin/options/resetButtonText', 'Reset to Defaults');

                    ?>

                    <p class="submit">
                        <input name="<?php esc_attr_e($dbKey) ?>[submit|<?php esc_attr_e( $page ); ?>|<?php echo esc_attr( $tab ); ?>]" type="submit" class="button-primary" value="<?php esc_attr_e($saveButtonText); ?>" />
                        <input name="<?php esc_attr_e($dbKey) ?>[reset|<?php esc_attr_e( $page ); ?>|<?php esc_attr_e( $tab ); ?>]" type="submit" class="button-secondary" value="<?php esc_attr_e($resetButtonText); ?>" />
                    </p>
                </form>
            </div>
            <?php
        }
    }

    public function sanitizeSettings() {

    }

 /* Setup section_callback */
 public function section_callback( $arguments ) {
    /* Set up input*/
    switch( $arguments['id'] ){
        case "categories" :
            echo "Categories that will trigger the member only message.";
            break;
        case "loginURL":
            echo "The login URL of your site. ";
        break;
    }
}
/* Create input fields*/
public function field_callback ( $arguments ) {
    echo "<input name=\"kuetemeier-essentials[test1][test2]\" id=\"categories\" type=\"text\" value=\"" .get_option("categories"). "\"\>";
}

    public function validateOptions($input) {

        // if we have no data, do nothing.
        if(empty($input)) {
            return array();
        }

        // for enhanced security, create a new empty array
        $valid_input = array();

        $submit = '';
		$page = '';
		$tab = '';

        // break up submit name for submit-type, page and tab
		foreach ( array_keys( $input ) as $key ) {
			if ((substr( $key, 0, 7 ) === 'submit|' ) || (substr( $key, 0, 6 ) === 'reset|')) {
                $parts = explode( '|', $key );
                $count = count( $parts );

                if ( $count > 0 ) {
                    $submit = $parts[0];
                    if ( $count > 1 ) {
                        $page = $parts[1];
                    }
                    if ( $count > 2 ) {
                        $tab = $parts[2];
                    }
                    break;
                }
            }
		}

        wp_die("Submit: $submit | Page: $page | Tab: $tab");
        wp_die("validate".$this->get('id'));
        return $valid_input; // return validated input
    }

    private function getPluginID() {
        return $this->get('config')->getPlugin()->getID();
    }

    private function getDBKey() {
        return $this->get('options')->getDBKey();
    }

}
