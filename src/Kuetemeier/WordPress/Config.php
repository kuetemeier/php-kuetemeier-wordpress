<?php

/**
 * Kuetemeier WordPress Plugin - Config
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

namespace Kuetemeier\WordPress;

// KEEP THIS for security reasons - blocking direct access to the PHP files by checking for the ABSPATH constant.
defined('ABSPATH') || die('No direct call!');


final class Config extends \Kuetemeier\Collection\Collection
{

    public function __construct($initValues = null)
    {
        parent::__construct($initValues);
    }


    public function getOptionsFromDB()
    {
        $db_key = $this->getDBKey();
        $this->set('_db-options', get_option($db_key));
    }


    public function getAllOptions()
    {
        return $this->get('_db-options');
    }


    public function init()
    {
        // test if options exists in DB, if not, create new ones from defaults
        if ($this->get('_db-options') === false) {
            $this->set('_default/version', $this->get('_plugin/version/this'), 1);
            update_option(
                $this->get('_plugin/options/key'), //key
                $this->get('_default'), // value
                1 // autoload
            );
        } else {
            $newValues = false;
            $dbOptions = $this->get('_db-options');
            foreach ($this->get('_default') as $key => $value) {
                if (!isset($dbOptions[$key])) {
                    $this->set('_db-options/' . $key, $value, 1);
                    $newValues = true;
                }
            }

            if ($newValues) {
                update_option(
                    $this->getDBKey(),
                    $this->get('_db-options'),
                    1
                );
            }
        }

        // TODO: Check for versions and different fields
    }


    public function getPlugin()
    {
        return $this->get('_pluginInstance');
    }


    public function getDefault($key, $module = '', $default = null)
    {
        if (empty($key)) {
            return $default;
        }
        if (empty($module)) {
            return $this->get('_default/' . $key, $default);
        } else {
            return $this->get('_default/' . $module . '/' . $key, $default);
        }
    }


    public function getOption($key, $module = '', $default = null)
    {
        if (empty($key)) {
            return $default;
        }
        if (empty($module)) {
            return $this->get('_db-options/' . $key, $default);
        } else {
            return $this->get('_db-options/' . $module . '/' . $key, $default);
        }
    }


    public function getOptionWithDefault($key, $module)
    {
        $ret = $this->getOption($key, $module, null);
        if ($ret === null) {
            $ret = $this->getDefault($key, $module);
        }
        return $ret;
    }


    public function setOption($key, $value, $module = '')
    {
        if (empty($key)) {
            return false;
        }
        if (empty($module)) {
            $this->set('_db-options/' . $key, $value, true);
        } else {
            $this->set('_db-options/' . $module . '/' . $key, $value, true);
        }
        $dbKey = $this->getDBKey();

        $result = update_option(
            $this->getDBKey(),
            $this->get('_db-options'),
            1
        );

        return $result;
    }


    public function getDBKey()
    {
        return $this->get('_plugin/options/key');
    }
}
