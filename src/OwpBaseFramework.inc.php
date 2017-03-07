<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpBaseFramework
 * @author    Brian Tafoya <btafoya@briantafoya.com>
 * @version   1.0
 * @license   MIT
 * @license   https://opensource.org/licenses/MIT The MIT License
 * @category  OpenWebPresence_Support_Library
 * @link      http://openwebpresence.com OpenWebPresence
 *
 * Copyright (c) 2017, Brian Tafoya
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


/**
 * This class is the glue that binds all of the Open Web Presence functionality together.
 * It acts as the controller, logic, and view handler without extreme complexity of an entire MVC framework.
 */
class OwpBaseFramework
{


    /**
     * Constructor
     *
     * @method void __construct()
     * @access public
     * @param object $frameworkObject Data and method oject created by the OwpFramework.
     * @global  string $root_path        The app root file path.
     * @global  string $current_web_root The current web root.
     * @global  object $PhpConsole       PhpConsole debugger object.
     * @uses OwpFramework
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __construct($frameworkObject)
    {
        $this->frameworkObject = $frameworkObject;

        $this->ezSqlDB = $frameworkObject["ezSqlDB"];
        $this->current_web_root = $frameworkObject["frameworkVariables"]["current_web_root"];
        $this->root_path = $frameworkObject["frameworkVariables"]["root_path"];
        $this->requested_action = $frameworkObject["frameworkVariables"]["requested_action"];
        $this->uuid = $frameworkObject["frameworkVariables"]["uuid"];
        $this->PhpConsole = $frameworkObject["PhpConsole"];
        $this->userClass = $frameworkObject["userClass"];
        $this->THEME = $frameworkObject["frameworkVariables"]["theme"];

        if(!in_array($this->requested_action, array("ajax", "jsAssets", "cssAssets"))) {
            /*
             * Dynamic Owp_request_ include
             */
            $modFileIncludeName = "Owp" . ucwords(strtolower($this->requested_action));
            $modFileLocation = $this->root_path . join(DIRECTORY_SEPARATOR, array("app","themes",$this->THEME,"mod", $modFileIncludeName . ".inc.php"));
            if (file_exists($modFileLocation)) {
                include $modFileLocation;
                $this->modMethods = new $modFileIncludeName($this->frameworkObject);
            } else {
                $this->modMethods = new OwpDefaultMod($this->frameworkObject, $modFileIncludeName, $modFileLocation);
            }

            /*
            if(class_exists($modFileIncludeName)) {
                $this->modAvailableMethods = get_class_methods($this->modMethods);
            }
            */
        }
    }

    /**
     * loadFooter()
     *
     * @method void loadFooter() Loads the footer based on the template setting.
     * @access private
     * @uses   $this->loadTemplate
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function loadFooter()
    {
        $this->loadTemplate("footer", "common");
    }

    /**
     * loadHeader()
     *
     * @method void loadHeader() Loads the header based on the template setting.
     * @access private
     * @uses   $this->loadTemplate
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function loadHeader()
    {
        $this->loadTemplate("header", "common");
    }

    /**
     * loadNav()
     *
     * @method void loadNav() Loads the nav based on the template setting.
     * @access private
     * @uses   $this->loadTemplate
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function loadNav()
    {
        $this->loadTemplate("nav", "common");
    }

    /**
     * loadTemplate()
     *
     * @method  void loadTemplate($template_name,$sub_dir) Loads the template based on the template setting.
     * @access  private
     * @param   string $template_name Template Name.
     * @param   string $sub_dir       Sub Directory
     * @returns boolean Loaded status
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function loadTemplate($template_name,$sub_dir = "pages")
    {
        $template = array();
        $template["theme"] = $this->root_path . join(DIRECTORY_SEPARATOR, array('app', 'themes', $_ENV["THEME"], "view", $sub_dir, $template_name . ".inc.php"));
        $template["default"] = $this->root_path . join(DIRECTORY_SEPARATOR, array('app', 'themes', 'default', "view", $sub_dir, $template_name . ".inc.php"));

        foreach($template as $tk => $tv) {
            if(file_exists($tv)) {
                $this->debugging["templates"][] = array($tk,$tv);
                include $tv;
                return true;
            }
        }

        return false;
    }

    /**
     * mod()
     *
     * @method void mod() Loads the nav based on the template setting.
     * @access protected
     * @uses   $this->mod()
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    protected function mod()
    {
        $class_name = "owp_mod_" . $this->requested_action;

        $mods["theme_functions"] = $this->root_path . join(DIRECTORY_SEPARATOR, array('app', 'themes', $_ENV["THEME"], 'mods', "owpFunctions.inc.php"));
        if(file_exists($mods["theme_functions"])) {
            include $mods["theme_functions"];
        }

        $mods = array();
        $mods["theme"] = $this->root_path . join(DIRECTORY_SEPARATOR, array('app', 'themes', $_ENV["THEME"], 'mods', 'pages', $class_name . ".inc.php"));
        $mods["default"] = $this->root_path . join(DIRECTORY_SEPARATOR, array('app', 'mods', 'pages', $class_name . ".inc.php"));

        foreach($mods as $tk => $tv) {
            if(file_exists($tv)) {
                $this->debugging["mods"][] = array($tk,$tv);
                include $tv;
                $tmpClass = new $class_name($this->frameworkObject);
            }
        }
    }

    /**
     * processAction()
     *
     * @method void processAction() Process the framework action.
     * @access private
     * @uses   $this->mod()
     * @uses   $this->loadTemplate()
     * @uses   $this->loadHeader()
     * @uses   $this->loadNav()
     * @uses   $this->loadFooter();
     * @uses   OwpAjaxUdf::processAction();
     * @throws Exception User class OwpAjaxUdf() does not exist.
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function processAction()
    {


        switch($this->requested_action) {
        default:
            $this->mod();
            $this->loadHeader();
            $this->loadNav();
            $this->loadTemplate($this->requested_action);
            $this->loadFooter();
            break;
        case "ajax":

            /*
            * Dynamic OwpAjaxUdf include
            */
            $modAjaxFileLocation = $this->root_path . join(DIRECTORY_SEPARATOR, array("app", "themes", $this->THEME, "lib", "OwpAjaxUdf.inc.php"));
            include $modAjaxFileLocation;

            if(class_exists("OwpAjaxUdf")) {
                $OwpAjaxUdf = new OwpAjaxUdf();
                $OwpAjaxUdf->processAction($this->frameworkObject);
            } else {
                throw new Exception("User class OwpAjaxUdf() does not exist.", 911);
            }
            break;
        case "jsAssets":
        case "cssAssets":
            $fileLocation = $this->root_path . join(DIRECTORY_SEPARATOR, array("app", "themes", $this->THEME, "lib", "Owp" . $this->requested_action . ".inc.php"));
            include $fileLocation;
            break;
        }
    }
}
