<?php

/**
 * Kuetemeier WordPress Plugin - Setting - SettingsBase
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


class SettingsBase extends \Kuetemeier\Collection\Collection
{

    const TPAGE = 'Page';
    const TTAB = 'Tab';
    const TSECTION = 'Section';
    const TOPTION = 'Option';

    const RPAGE = '_registered/pages';
    const RTAB = '_registered/tabs';
    const RSECTION = '_registered/sections';
    const ROPTION = '_registered/options';


    const REGISTERED_OPTIONS = array(
        self::RPAGE => self::TPAGE,
        self::RTAB => self::TTAB,
        self::RSECTION => self::TSECTION,
        self::ROPTION => self::TOPTION
    );


    const CONFIG_ALIASES = array(
        'subpage' => 'page',
        'tab' => 'tabs',
        'section' => 'sections'
    );


    const CONFIG_DEFAULTS = array(
        'priority' => 100,
        'capability' => 'manage_options'
    );


    /**
     * Constructor
     *
     * Valid config options:
     *
     * id           : Unique (for the type) ID of this setting.
     * slug         : (Defaults to ID) Page, Menu, ID slug.
     * tabs         : The tab IDs this Setting should register to.
     * page         : The page ID this Setting should register to.
     * subpage      : alias for 'page'
     */
    public function __construct($settingsConfig, $required = array(), $defaults = array())
    {

        // replace aliases if originals are not defined
        foreach (self::CONFIG_ALIASES as $alias => $orig) {
            if ((isset($settingsConfig[$alias])) && (!isset($settingsConfig[$orig]))) {
                $settingsConfig[$orig] = $settingsConfig[$alias];
                unset($settingsConfig[$alias]);
            }
        }

        // TODO: add $defaults to loop
        foreach (self::CONFIG_DEFAULTS as $key => $value) {
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

        // 'sections' not empty
        if (!isset($settingsConfig['sections'])) {
            $settingsConfig['sections'] = array();
        }

        // ensure 'sections' is an array
        if (!is_array($settingsConfig['sections'])) {
            $settingsConfig['sections'] = array($settingsConfig['sections']);
        }

        // convert to hash for faster lookups
        if (!empty($settingsConfig['sections'])) {
            $settingsConfig['sections'] = array_flip($settingsConfig['sections']);
        }

        parent::__construct($settingsConfig);

        // set dynamic defaults:
        $this->set('slug', $this->get('id'), false);

        array_push($required, 'id');

        foreach ($required as $r) {
            if (!($this->has($r))) {
                $this->wpDieError(' MUST have a "' . $r . '"!');
            }
        }

        if (!$this->has('displayFunction')) {
            $this->set('displayFunction', array(&$this, 'callbackDefaultDisplayFunction'));
        }

        foreach (array_keys(self::REGISTERED_OPTIONS) as $roption) {
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
    public function callbackDefaultDisplayFunction($args)
    {
        if ($this->hasContent()) {
            ?>
            <div id="<?php echo esc_attr($this->getID()); ?>">
                <?php $this->echoContent() ?>
            </div>
            <?php
        }
    }


    public function callbackAdminMenu($config)
    {
        return; // placeholder
    }


    public function wpDieError($message, $errorType = 'ERROR')
    {
        wp_die(esc_html($errorType) . ' - ' . esc_html(get_class($this)) . ' "' . esc_html($this->get('id', 'UNDEFINED')) . '": ' . esc_html($message));
    }


    public function getID()
    {
        return $this->get('id');
    }


    public function getPriority()
    {
        return $this->get('priority', 100);
    }


    public function getTitle()
    {
        return $this->get('title');
    }


    public function getModule()
    {
        return $this->get('module');
    }


    public function register($type, $item)
    {
        if (!isset(self::REGISTERED_OPTIONS[$type])) {
            $this->wpDieError('Unknown type "' . esc_html($type) . '". Cannot register "' . esc_html($item . getID()) . '".');
        }

        $itemCollection = $this->get($type);
        $itemID = $item->getID();

        if ($itemCollection->has($itemID)) {
            $this->wpDieError(html_esc(self::REGISTERED_OPTIONS[$type]) . ' with id "' . esc_html($sectionID) . '" is already registered');
        }

        $this->get($type)->set($itemID, $item->getPriority(), $item);
        $item->successfullyRegisteredWith($this);
    }


    public function registerSection($section)
    {
        $this->register(self::RSECTION, $section);
    }


    public function getRegisteredSections()
    {
        return $this->get(self::RSECTION);
    }


    public function registerTab($tab)
    {
        $this->register(self::RTAB, $tab);
    }


    public function getRegisteredTabs()
    {
        return $this->get(self::RTAB);
    }


    public function registerOption($option)
    {
        $this->register(self::ROPTION, $option);
    }


    public function getRegisteredOptions()
    {
        return $this->get(self::ROPTION);
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
        return $this->get('config')->get('_pluginInstance');
    }


    public function getPluginOptions()
    {
        return $this->get('config')->get('_optionsInstance');
    }


    public function getPluginModules()
    {
        return $this->getPlugin()->getModules();
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


    public function getSections()
    {
        $sections = $this->get('sections');
        if (empty($sections)) {
            // Return an empty array, even if tabs are not set.
            return array();
        }
        return array_keys($sections);
    }


    public function registerMeOn($optionTypes, $isOption = false)
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
                        $this->wpDieError('registerMeOn - Page "' . esc_html($page) . '" is not defined.');
                    } else {
                        if ($isOption) {
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
                    }
                    break;
                case self::TTAB:
                    // get the tabs from the config value (not the registered to!)
                    $tabs = $this->getTabs();

                    foreach ($tabs as $tab) {
                        $tabObject = $options->getTab($tab);
                        if (!isset($tabObject)) {
                            $this->wpDieError('registerMeOn - Tab "' . esc_html($tab) . '" is not defined.');
                        } else {
                            if ($isOption) {
                            } else {
                                switch (get_class($this)) {
                                    case 'Kuetemeier\WordPress\Settings\Section':
                                        $tabObject->registerSection($this);
                                        $registerSuccess = true;
                                        break;
                                }
                            }
                        }
                    }
                    break;
                case self::TSECTION:
                    // get the sections from the config value (not the registered to!)
                    $sections = $this->getSections();

                    foreach ($sections as $section) {
                        $sectionObject = $options->getSection($section);
                        if (!isset($sectionObject)) {
                            $this->wpDieError('registerMeOn - Section "' . esc_html($section) . '" is not defined.');
                        } else {
                            if ($isOption) {
                                $sectionObject->registerOption($this);
                                $registerSuccess = true;
                            } else {
                                switch (get_class($this)) {
                                    case 'Kuetemeier\WordPress\Settings\Option':
                                        $sectionObject->registerOption($this);
                                        $registerSuccess = true;
                                        break;
                                }
                            }
                        }
                    }
                    break;

                default:
                    $this->wpDieError('registerMeOn - unknown optionType "' . esc_html($optionType) . '"');
            }
        }
        if (!$registerSuccess) {
            $this->wpDieError('registerMeOn - Could not register "' . esc_html($this->getID()) . '" on "' . esc_html(join(', ', $optionTypes)) . '"');
        }
        return $registerSuccess;
    }


    public function getValue()
    {
        return $this->get('config')->getOptionWithDefault($this->getID(), $this->getModule());
    }


    public function hasContent()
    {
        return $this->has('content');
    }


    public function getContent($default = '')
    {
        $content = $this->get('content', $default);

        if (empty($content)) {
            return $default;
        }

        if (is_string($content)) {
            return $content;
        } elseif (is_callable($content)) {
            $ret = $content($this);
            if (isset($ret)) {
                return $ret;
            } else {
                return '';
            }
        } elseif (is_array($content)) {
            $ret = '';
            foreach ($content as $item) {
                if (is_string($item)) {
                    $ret .= $item;
                } elseif (is_callable($item)) {
                    $r = $item($this);
                    if (isset($r)) {
                        $ret .= $r;
                    }
                }
            }
            return $ret;
        } else {
            $this->wpDieError('content is not a string, a callable or an array.');
        }

        // Never reached... but safety first.
        return '';
    }


    public function getLabel($default = '')
    {
        return $this->get('label', $default);
    }


    public function getDescription($default = '')
    {
        return $this->get('description', $default);
    }


    public function getEscText($content)
    {
        if (is_string($content)) {
            if ($this->get('markdown', false)) {
                $content = $this->markdownLimited($content);
            } else {
                $content = esc_html($content);
                $content = str_replace('\n', '<br />', $content);
            }
            return $content;
        }
    }


    public function echoText($content)
    {
        // phpcs:disable WordPress.XSS.EscapeOutput
        // $esc_html contains only escaped content.
        echo $this->getEscText($content);
        // phpcs:enable WordPress.XSS.EscapeOutput
    }


    public function echoContent()
    {
        $this->echoText($this->getContent());
    }


    /**
     * Limited Markdown converter.
     *
     * @see https://gist.github.com/rohit00082002/2773368
     */
    protected function markdownLimited($text)
    {
        // Make it HTML safe for starters
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        // Replace for spaces with a tab (for lists and code blocks)
        $text = str_replace("    ", "\t", $text);
        // Blockquotes (they have email-styled > at the start)
        $regex = '^&gt;.*?$(^(?:&gt;).*?\n|\n)*';
        preg_match_all("~$regex~m", $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $set) {
            $block = "<blockquote>\n" . trim(preg_replace('~(^|\n)[&gt; ]+~', "\n", $set[0])) . "\n</blockquote>\n";
            $text = str_replace($set[0], $block, $text);
        }
        // Titles
        $text = preg_replace_callback("~(^|\n)(#{1,6}) ([^\n#]+)[^\n]*~", function ($match) {
            $n = strlen($match[2]);
            return "\n<h$n>" . $match[3] . "</h$n>";
        }, $text);
        // Lists must start with a tab (four spaces are converted to tabs ^above^)
        $regex = '(?:^|\n)(?:\t+[\-\+\*0-9.][^\n]+\n+)+';
        preg_match_all("~$regex~", $text, $matches, PREG_SET_ORDER);
        // Recursive closure
        $list = function ($block, $top_level = false) use (&$list) {
            if (is_array($block)) {
                $block = $block[0];
            }
            // Chop one level of all the lines
            $block = preg_replace("~(^|\n)\t~", "\n", $block);
            // Is this an ordered or un-ordered list?
            $tag = ctype_digit(substr(ltrim($block), 0, 1)) ? 'ol' : 'ul';
            // Only replace elements of THIS LEVEL with li
            $block = preg_replace('~(?:^|\n)[^\s]+ ([^\n]+)~', "\n<li>$1</li>", $block);
            if ($top_level) {
                $block .= "\n";
            }
            $block = "<$tag>$block</$tag>";
            // Replace nested list items now
            $block = preg_replace_callback('~(\t[^\n]+\n?)+~', $list, $block);
            // return the finished list
            return $top_level ? "\n$block\n\n" : $block;
        };
        foreach ($matches as $set) {
            $text = str_replace($set[0], $list(trim($set[0], "\n "), true), $text);
        }
        // Paragraphs
        $text = preg_replace('~\n([^><\t]+)\n~', "\n\n<p>$1</p>\n\n", $text);
        // Paragraphs (what about fixing the above?)
        $text = str_replace(array("<p>\n", "\n</p>"), array('<p>', '</p>'), $text);
        // Lines that end in two spaces require a BR
        $text = str_replace("  \n", "<br>\n", $text);
        // Bold, Italic, Code
        $regex = '([*_`])((?:(?!\1).)+)\1';
        preg_match_all("~$regex~", $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $set) {
            if ($set[1] == '`') {
                $tag = 'code';
            } elseif ($set[1] == '*') {
                $tag = 'b';
            } else {
                $tag = 'em';
            }
            $text = str_replace($set[0], "<$tag>{$set[2]}</$tag>", $text);
        }
        // Links and Images
        $regex = '(!)*\[([^\]]+)\]\(([^\)]+?)(?: &quot;([\w\s]+)&quot;)*\)';
        preg_match_all("~$regex~", $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $set) {
            $title = isset($set[4]) ? " title=\"{$set[4]}\"" : '';
            if ($set[1]) {
                $text = str_replace($set[0], "<img src=\"{$set[3]}\"$title alt=\"{$set[2]}\"/>", $text);
            } else {
                $text = str_replace($set[0], "<a href=\"{$set[3]}\"$title>{$set[2]}</a>", $text);
            }
        }
        // Preformated (often code) blocks
        $regex = '(?:(?:(    |\t)[^\n]*\n)|\n)+';
        preg_match_all("~$regex~", $text, $matches, PREG_SET_ORDER);
        foreach ($matches as $set) {
            if (!trim($set[0])) {
                continue;
            }
            // If any tags were added (i.e. <p></p>), remove them!
            $lines = strip_tags($set[0]);
            // Remove the starting tab from each line
            $lines = trim(str_replace("\n\t", "\n", $lines), "\n");
            // Mark strings
            $regex = '((&#039;)|(&quot;))(?:[^\\\\1]|\\\.)*?\1';
            $lines = preg_replace("~$regex~", '<span class="string">$0</span>', $lines);
            // Mark comments
            $regex = '(/\*.*?\*/)|((#(?!\w+;)|(-- )|(//))[^\n]+)';
            $lines = preg_replace("~$regex~s", '<span class="comment">$0</span>', $lines);
            $text = str_replace($set[0], "\n<pre>" . $lines . "</pre>\n", $text);
        }
        // Reduce crazy newlines
        return preg_replace("~\n\n\n+~", "\n\n", $text);
    }
}
