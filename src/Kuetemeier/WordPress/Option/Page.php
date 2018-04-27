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

    public function display_tabs() {
        $tabs = $this->get('tabs');
        $keys = $tabs->keys();

		if (count($keys) > 0 ) {

            $currentTab = $this->get('config')->get('options')->getCurrentTab();
            if (empty($currentTab) || !$tabs->has($currentTab)) {
                $currentTab = $keys[0];
            }

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
		}

    }

    public function replaceBySubPage($subPage) {
        $this->replaceBySubPage = $subPage;
    }

    public function callback__defaultDisplayFunction($args)
    {
        if (empty($this->replaceBySubPage)) {

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
                    $this->display_tabs()
                ?>
                <?php settings_errors(); ?>

                <form method="post" action="options.php">
                    <?php
                    //settings_fields( $page_slug );
                    //do_settings_sections( $page_slug );
                    ?>

                    <p class="submit">
                        <input name="kuetemeier-essentials[submit|<?php echo esc_attr( $page_slug ); ?>|<?php echo esc_attr( $tab ); ?>]" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'kuetemeier-essentials' ); ?>" />
                        <input name="kuetemeier-essentials[reset-<?php echo esc_attr( $tab ); ?>]" type="submit" class="button-secondary" value="<?php esc_attr_e( 'Reset Defaults', 'kuetemeier-essentials' ); ?>" />
                    </p>
                </form>
            </div>
            <?php
        }
	}

}
