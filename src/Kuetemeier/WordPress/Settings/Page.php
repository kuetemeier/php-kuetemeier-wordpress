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


class Page extends SettingsBase {


    private $replaceBySubPage = null;


    public function __construct($pageConfig, $required = array('id', 'title')) {

        parent::__construct($pageConfig, $required);

        $this->set('menuTitle', $this->get('title'), false);

        add_action('admin_init', array(&$this, 'callback__admin_init'));

    }


    public function callback__admin_init() {

        $currentPage = $this->getCurrentPage();

        // empty for option page submit
        // TODO, test if nessesariy
        if (empty($currentPage) || $currentPage === $this->getId()) {

            $this->getRegisteredSections()->foreach(
                function($key, $section) {
                    $section->adminInitFromPage($this);
                }
            );

            $this->getRegisteredTabs()->foreach(
                function($key, $tab) {
                    $tab->adminInitFromPage($this);
                }
            );
        }

        $slug = $this->get('slug', $this->get('id'));
        $dbKey = $this->getDBKey();
        //register_setting($slug, $dbKey, array(&$this, 'validateOptions'));
        register_setting($slug, $dbKey, array($this->getPluginOptions(), 'validateOptions'));
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


    public function displayTabs($currentTab) {
        $tabs = $this->get('_registered/tabs');
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


    public function replaceBySubPage($subPage) {
        $this->replaceBySubPage = $subPage;
    }


    public function getCurrentPage() {
        return ( isset( $_GET['page'] ) ? sanitize_key( $_GET['page'] ) : '' );
    }


    public function getCurrentTab() {
        $currentTab =  ( isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : '' );;
        $tabs = $this->getRegisteredTabs();
        if (empty($currentTab) || !$tabs->has($currentTab)) {
            if ($tabs->count() > 0) {
                $currentTab = $tabs->keys()[0];
            } else {
                $currentTab = '';
            }
        }
        return $currentTab;
    }


    public function callback__defaultDisplayFunction($args)
    {
        if (empty($this->replaceBySubPage)) {
            $page = $this->get('slug');


            $tab = $this->getCurrentTab();

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
                    do_settings_sections( $page );
                    $this->displayTabs($tab);
                ?>
                <?php settings_errors(); ?>

                <form method="post" action="options.php">
                    <?php
                    settings_fields( $page );
                    do_settings_sections( $page.'-t-'.$tab );
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


    public function sanitizeSettings()
    {

    }


    public function validateOptions($input, $validInput, $tabID)
    {
        $registeredOptions = $this->getRegisteredOptions();

        foreach($registeredOptions->keys() as $key) {
            $option = $registeredOptions->get($key);
            $validInput = $option->validateOptions($input, $validInput);
        }

        $registeredTabs = $this->getRegisteredTabs();

        if ($registeredTabs->has($tabID)) {
            $tab = $registeredTabs->get($tabID);

            $validInput = $tab->validateOptions($input, $validInput);
        }

        return $validInput;
    }
}
