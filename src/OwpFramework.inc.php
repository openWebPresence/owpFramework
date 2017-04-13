<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpFramework
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
 * OwpFramework Bootstrap Class.
 * Open Presence Framework Class Bootstrap.
 */
class OwpFramework
{

    /**
     * @var string $root_path Set the root file path.
     */
    static public $root_path = null;

    /**
     * @var array $debugging Debugging array.
     */
    static public $debugging = null;

    /**
     * @var boolean $debug Is debugging enabled by default.
     */
    static public $debug = false;

    /**
     * @var int $userID UserID.
     */
    static public $userID = 0;

    /**
     * @var string $uuid Unique uuid().
     */
    static public $uuid = null;

    /**
     * @var object $ezSqlDB The ezSQL Database Object.
     */
    static public $ezSqlDB;

    /**
     * @var string $current_web_root The web root url.
     */
    static public $current_web_root;

    /**
     * @var object $OwpSupportMethods OpenWebPresence support methods.
     */
    static public $OwpSupportMethods;

    /**
     * @var string $default_action Default action.
     */
    static public $default_action = "home";

    /**
     * @var string $requested_action The requested action.
     */
    static public $requested_action;

    /**
     * @var string $modMethods Mod methods object.
     */
    static public $modMethods = null;

    /**
     * @var string $modAvailableMethods Mod methods available.
     */
    static public $modAvailableMethods = array();

    /**
     * @var string $theme Active theme.
     */
    static public $theme = null;

    /**
     * @var object $ezSqlDB ezSQL Database Object.
     */
    static public $userClass;

    /**
     * @var object $frameworkObject Framework Class Object.
     */
    static public $frameworkObject;

    /**
     * @var object $defaultAction Default function.
     */
    static public $defaultAction;

    /**
     * @var object $actionsConfig Default config.
     */
    static public $actionsConfig;

    /**
     * @var object $actionsConfig Default config.
     */
    static public  $actionsConfigFileLocation;


    /**
     * Constructor
     *
     * @method void __construct()
     * @access public
     * @throws Exception Missing theme config directive file.
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->build();
    }//end __construct()


    /**
     * build
     *
     * @method void build() Builds the framework objects
     * @return array|object
     * @throws Exception
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function build()
    {

        /*
            * Set the root path
         */
        self::$root_path = ROOT_PATH;

        /*
            * Set the current web root
        */
        self::$current_web_root = CURRENT_WEB_ROOT;

        /*
            * Generate $uuid based on OwpSupportMethods->uuid().
         */
        $_SESSION["uuid"] = OwpSupportMethods::uuid();
        self::$uuid = $_SESSION["uuid"];

        /*
            * Set the theme
         */
        self::$theme = $_ENV["THEME"];

        /*
            * Init the debugger
         */
        self::$debug = ((int) $_ENV["ISDEV"] ? true : false);

        /*
            * Init the database class
         */
        self::$ezSqlDB = new OwpEzSqlMysql($_ENV["DB_USER"], $_ENV["DB_PASS"], $_ENV["DB_NAME"], $_ENV["DB_HOST"]);
        self::$ezSqlDB->use_disk_cache = false;
        self::$ezSqlDB->cache_queries  = false;
        self::$ezSqlDB->hide_errors();

        /*
            * Default Request Definition
         */
        self::$defaultAction = [
                                 "none"    => "home",
                                 "isUser"  => "home",
                                 "isAdmin" => "home",
                                ];

        /*
            * Default Action Definition
         */
        self::$actionsConfig = [
                                "404" => [
                                          "view"        => [
                                                            "app/themes/default/common/header.inc.php",
                                                            "app/themes/default/common/nav.inc.php",
                                                            "app/themes/default/pages/404.inc.php",
                                                            "app/themes/default/common/footer.inc.php",
                                                           ],
                                          "mod"         => [],
                                          "js"          => [],
                                          "css"         => [],
                                          "permissions" => [
                                                            "isUser"  => 0,
                                                            "isAdmin" => 0,
                                                           ],
                                         ],
                               ];

        /*
            * ActionsConfigFileLocation
         */
        $actionsConfig = null;
        $defaultAction = null;
        self::$actionsConfigFileLocation = self::$root_path.join(DIRECTORY_SEPARATOR, array("app", "themes", self::$theme, "actionsConfig.inc.php"));

        if (file_exists(self::$actionsConfigFileLocation)) {
            include  self::$actionsConfigFileLocation;
            self::$actionsConfig = $actionsConfig;
            self::$defaultAction = $defaultAction;
        } else {
            throw new Exception("Missing ". self::$actionsConfigFileLocation."!", 911);
        }

        /*
            * Create the frameworkObject
        */
        self::$frameworkObject = array(
                                  "frameworkVariables" => array(
                                                           "theme"            => self::$theme,
                                                           "current_web_root" => (string) self::$current_web_root,
                                                           "root_path"        => (string) self::$root_path,
                                                           "requested_action" => (string) self::$requested_action,
                                                           "uuid"             => (string) self::$uuid,
                                                          ),
                                  "ezSqlDB"            => (object) self::$ezSqlDB,
                                  "mod_data"           => new OwpMod(),
                                 );

        /*
            * Load the user class
         */
        self::$userClass = new OwpUsers(self::$frameworkObject);
        self::$userClass->rememberMe();

        /*
            * Add it to the $frameworkObject array.
         */
        self::$frameworkObject["userClass"] = (object) self::$userClass;

        /*
            * Set the requested action
         */
        if(self::$userClass->isLoggedIn()) {
            if(self::$userClass->isAdmin()) {
                self::$default_action = self::$defaultAction["isAdmin"];
            } else {
                self::$default_action = self::$defaultAction["isUser"];
            }
        } else {
            self::$default_action = self::$defaultAction["none"];
        }

        self::$requested_action = (isset($_GET["_route_"]) ? OwpSupportMethods::filterAction($_GET["_route_"]) : self::$default_action);

        self::$frameworkObject["frameworkVariables"]["requested_action"] = (string) self::$requested_action;
        self::$frameworkObject["frameworkVariables"]["default_action"] = (string) self::$default_action;
        self::$frameworkObject["actionsConfig"] = (array) self::$actionsConfig;
        self::$frameworkObject["defaultAction"] = (array) self::$defaultAction;

        return self::$frameworkObject;

    }//end build()


    /**
     * __get($name)
     *
     * @method __GET($name) Getter
     * @param  $name
     * @return mixed
     */
    public function __get($name)
    {
        if(isset(self::$frameworkObject)) {
            return self::$frameworkObject;
        }
        if(isset($this->$name)) {
            return $this->$name;
        }

        self::build();
        if(isset(self::$frameworkObject)) {
            return self::$frameworkObject;
        }
        if(isset($this->$name)) {
            return $this->$name;
        }

        throw new InvalidArgumentException($name." does not exist!");

    }//end __get()


    /**
     * getFrameworkObject().
     * Return the entire framework object for reference.
     *
     * @method getFrameworkObject() Return the entire framework object for reference.
     * @return array|object
     */
    public function getFrameworkObject()
    {
        return $this->frameworkObject;

    }//end getFrameworkObject()


    /**
     * Debug
     *
     * @method void __debugInfo()
     * @access public
     * @return object
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo()
    {
        return $this->frameworkObject;

    }//end __debugInfo()


}//end class
