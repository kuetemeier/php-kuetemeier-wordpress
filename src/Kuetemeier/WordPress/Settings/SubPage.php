<?php

/**
 * Kuetemeier WordPress Plugin - Setting - SubPage
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


class SubPage extends Page
{

    public function __construct($pageConfig)
    {

        // shortcut 'parent' for 'parentSlug'
        if (isset($pageConfig['parent'])) {
            $pageConfig['parentSlug'] = $pageConfig['parent'];
        }

        parent::__construct($pageConfig, array('id', 'parentSlug', 'title'));

        if ($this->get('id') === $this->get('parentSlug')) {
            $page = $this->getPluginOptions()->getPage($this->get('parentSlug'));

            $page->replaceBySubPage($this);
        }
    }

    public function callbackAdminMenu($config)
    {
        add_submenu_page(
            // parent_slug - The slug name for the parent menu (or the file name of a standard WordPress admin page).
            $this->get('parentSlug'),
            // page_title - The text to be displayed in the title tags of the page when the menu is selected.
            $this->get('title'),
            // menu_title - The text to be used for the menu.
            $this->get('menuTitle'),
            // capability - The capability required for this menu to be displayed to the user.
            $this->get('capability'),
            // menu_slug - The slug name to refer to this menu by. Should be unique for this menu and only include lowercase alphanumeric, dashes, and underscores characters to be compatible with sanitize_key().
            $this->get('slug'),
            // display function
            $this->get('displayFunction')
        );
    }
}
