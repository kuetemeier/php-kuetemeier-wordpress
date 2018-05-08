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


abstract class Option extends SettingsBase
{
	public function __construct($optionConfig) {
        parent::__construct($optionConfig);

        $this->registerMeOn(array(SettingsBase::TPAGE, SettingsBase::TTAB, SettingsBase::TSECTION), true);
    }

    public function adminInitFromSection($page, $section, $sectionID, $pageID)
    {
        add_settings_field(
            $sectionID.'-o-'.$this->getID(), // id
            $this->getTitle(), // title
            array(&$this, 'defaultDisplay'), // display function
            $pageID,
            $sectionID
        );
    }

    public function getDefault() {
        return $this->get('config')->getDefault($this->getID(), $this->getModule());
    }

	/**
	 * Helper function for callbackk__display_setting, returns html for the label.
	 *
	 * @param string $composedID A composed id for the html id fields.
	 *
	 * @return string HTML or '', if label property is empty.
	 *
	 * @since 0.2.1
	 */
    protected function getHTMLDisplayLabelFor($composedID)
    {

        $label = $this->getLabel();
		if (empty($label)) {
			return '';
		}

		$escID = esc_attr( $composedID );
		return '<label id="' . $escID . '-label" for="' . $escID . '"> ' . $this->getEscText($label) . '</label>';

    }


	/**
	 * Helper function for callbackk__display_setting, returns html for the description.
	 *
	 * @param string $composed_id A composed id for the html id fields.
	 *
	 * @return string HTML or '', if description property is empty.
	 *
	 * @since 0.2.1
	 */
    protected function getHTMLDescription($composedID)
    {
        $description = $this->getDescription();
		if (empty($description)) {
			return '';
		}

		$escID = esc_attr($composedID);

		return '<p class="description" id="' . $escID . '-description">' . $this->getEscText($description) . '</p>';
	}


    abstract public function sanitize($input);


    /**
     * The value for the Field if it is not set.
     *
     * For example: 0 or ''
     * BUT NEVER 'null'! (this is needed internally)
     */
    abstract public function getEmptyValue();


    public function validateOptions($input, $validInput)
    {
        $key = $this->getID();
        $module = $this->getModule();

        if (isset($input[$module])) {
            if (isset($input[$module][$key])) {
                $value = $input[$module][$key];

                $sanitized = $this->sanitize($value);

                if (!isset($validInput[$module])) {
                    $validInput[$module] = array();
                }
                $validInput[$module][$key] = $sanitized;

            } else {
                $validInput[$module][$key] = $this->getEmptyValue();
            }
        } else {
            $validInput[$module][$key] = $this->getEmptyValue();
        }

        return $validInput;
    }


    public function sanitizeText($input, $multiLine=false)
    {
		if ( ! isset( $input ) ) {
			return $this->getEmptyValue();
		}

        if ($this->get('allowScripts', false)) {
            $allowedTags = wp_kses_allowed_html('post');
            $allowedTags['script'] = array(
                'async' => true,
                'charset' => true,
                'defer' => true,
                'src' => true,
                'type' => true
            );

            return wp_kses($input, $allowedTags);
        }

        if ($this->get('allowHTML', false)) {
            return wp_kses_post($input);
        }

        if ($multiLine) {
            return sanitize_textarea_field($input);
        }

        // default:
        return sanitize_text_field($input);
    }


    public function displayInput($type, $baseClass)
    {
		// Get current value.
		$value = $this->getValue();

		// Assemble a compound and escaped id string.
		$escID = esc_attr($this->getID());
		// Assemble an escaped name string. The name attribute is importan, it defines the keys for the $input array in validation.
        $escName = esc_attr($this->getDBKey().'['.$this->getModule() . '][' . $this->getID() . ']' );

        $class = $baseClass;

		// Compose output.
		$escHtml = '<input type="'.esc_attr($type).'" id="' . $escID . '" name="' . $escName . '" value="' . esc_attr( $value ) . '" class="'.esc_attr($class).'" />';
		$escHtml .= $this->getHTMLDisplayLabelFor($escID);
		$escHtml .= $this->getHTMLDescription($escID);

		// phpcs:disable WordPress.XSS.EscapeOutput
		// $esc_html contains only escaped content.
		echo $escHtml;
		// phpcs:enable WordPress.XSS.EscapeOutput
    }
}
