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
     * @var array $LoadedClasses Object holder for dynamically loaded classes
     */
    public $LoadedClasses = array();

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
        $actionsConfig = null;

        $this->frameworkObject = $frameworkObject;

        $this->ezSqlDB = $frameworkObject["ezSqlDB"];
        $this->current_web_root = $frameworkObject["frameworkVariables"]["current_web_root"];
        $this->root_path = $frameworkObject["frameworkVariables"]["root_path"];
        $this->requested_action = $frameworkObject["frameworkVariables"]["requested_action"];
        $this->uuid = $frameworkObject["frameworkVariables"]["uuid"];
        $this->PhpConsole = $frameworkObject["PhpConsole"];
        $this->userClass = $frameworkObject["userClass"];
        $this->THEME = $frameworkObject["frameworkVariables"]["theme"];
        $this->actionsConfig = $frameworkObject["actionsConfig"];

        switch($this->requested_action) {
            default:
                $hasPermission = $this->checkActionPermissions($this->requested_action);

                $this->processAction(($hasPermission?$this->requested_action:$frameworkObject["frameworkVariables"]["default_action"]));
                break;
            case "ajaxDefault":
                include ($this->root_path.join(DIRECTORY_SEPARATOR, array("app","themes","default","lib","OwpAjaxUdf.inc.php")));
                break;
            case "jsAssetsDefault":
                include ($this->root_path.join(DIRECTORY_SEPARATOR, array("app","themes","default","lib","OwpjsAssets.inc.php")));
                break;
            case "cssAssetsDefault":
                include ($this->root_path.join(DIRECTORY_SEPARATOR, array("app","themes","default","lib","OwpcssAssets.inc.php")));
                break;
            case "ajax":
                include ($this->root_path.join(DIRECTORY_SEPARATOR, array("app","themes",$this->THEME,"lib","OwpAjaxUdf.inc.php")));
                $OwpAjaxUdf = new OwpAjaxUdf();
                $OwpAjaxUdf->processAction($frameworkObject);
                break;
            case "jsAssets":
                include ($this->root_path.join(DIRECTORY_SEPARATOR, array("app","themes",$this->THEME,"lib","OwpjsAssets.inc.php")));
                break;
            case "cssAssets":
                include ($this->root_path.join(DIRECTORY_SEPARATOR, array("app","themes",$this->THEME,"lib","OwpcssAssets.inc.php")));
                break;
        }
    }

    /**
     * checkActionPermissions()
     *
     * @method checkActionPermissions() Returns action data
     * @param $action
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function checkActionPermissions($action)
    {
        $getActionData = $this->getActionData($action);

        if($this->userClass->isLoggedIn()) {
            if($this->userClass->isAdmin() && (int)$getActionData["isAdmin"]) {
                return true;
            } else {
                return ((int)$getActionData["isUser"]?true:false);
            }
        } else {
            false;
        }
    }

    /**
     * getActionData()
     *
     * @method getActionData() Returns action data
     * @param $action
     * @return mixed
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function getActionData($action)
    {
        return (isset($this->actionsConfig[$action])?$this->actionsConfig[$action]:false);
    }

    /**
     * processAction()
     *
     * @method void processAction() Process the framework action.
     * @access private
     * @param string $action
     * @throws Exception Include does not exist.
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function processAction($action)
    {
        $actionData = $this->getActionData($action);

        if(!$actionData) {
            $actionData = $this->getActionData("404");
        }

        $this->PhpConsole->debug($actionData, "OwpBaseFramework->processAction()->actionData");

        $mod_includes = array();
        $mod_includes[] = $this->root_path . join(DIRECTORY_SEPARATOR, array('app', 'themes', $_ENV["THEME"], 'lib', "owpFunctions.inc.php"));

        if($actionData && $actionData["mod"]) {
            $mod_includes = array_merge($mod_includes, $actionData["mod"]);
        }

        if($mod_includes) { foreach($mod_includes as $mi) {
            if(file_exists($mi)) {
                $classes = OwpSupportMethods::file_get_php_classes($mi);
                include $mi;
                foreach($classes as $class_name) {
                    $this->LoadedClasses[$class_name] = new $class_name($this->frameworkObject);
                }
            } else {
                throw new Exception("Mod include " . $mi . " not found!", 911);
            }
        }}

        $view_includes = array();
        if($actionData && $actionData["view"]) {
            $view_includes = array_merge($view_includes, $actionData["view"]);
        }

        if($view_includes) { foreach($view_includes as $vi) {
            if(file_exists($vi)) {
                include $vi;
            } else {
                throw new Exception("View include " . $vi . " not found!", 911);
            }
        }}
    }
}
