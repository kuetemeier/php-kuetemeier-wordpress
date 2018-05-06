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

namespace Kuetemeier\WordPress;

/*********************************
 * KEEP THIS for security reasons
 * blocking direct access to our plugin PHP files by checking for the ABSPATH constant
 */
defined( 'ABSPATH' ) || die( 'No direct call!' );


final class Config extends \Kuetemeier\Collection\Collection {

    public function __construct($initValues = null) {
        parent::__construct($initValues);
    }

    public function getOptionsFromDB() {
        $db_key = $this->get('plugin/options/key');
        $this->set('_db-options', get_option($db_key));
    }

    public function init() {
        // TODO: init db, if values not found.
    }

    public function getPlugin() {
        return $this->get('_/plugin');
    }

    public function getDefault($key, $module='', $default=null) {
        if (empty($key)) {
            return $default;
        }
        if (empty($module)) {
            return $this->get('_default/'.$key, $default);
        } else {
            return $this->get('_default/'.$module.'/'.$key, $default);
        }
    }


    public function getOption($key, $module='', $default=null) {
        if (empty($key)) {
            return $default;
        }
        if (empty($module)) {
            return $this->get('_db-options/'.$key, $default);
        } else {
            return $this->get('_db-options/'.$module.'/'.$key, $default);
        }
    }


    public function getOptionWithDefault($key, $module)
    {
        $ret =  $this->getOption($key, $module, null);
        if (!isset($ret)) {
            $ret = $this->getDefault($key, $module);
        }
        return $ret;
    }
}
