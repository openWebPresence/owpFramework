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
class OwpFramework {

    /**
     * @var string $root_path Set the root file path.
     */
    public $root_path = null;

    /**
     * @var array $debugging Debugging array.
     */
    public $debugging = null;

    /**
     * @var boolean $debug Is debugging enabled by default.
     */
    public $debug = false;

    /**
     * @var int $userID UserID.
     */
    public $userID = 0;

    /**
     * @var string $uuid Unique uuid().
     */
    public $uuid = null;

    /**
     * @var object $ezSqlDB The ezSQL Database Object.
     */
    protected $ezSqlDB;

    /**
     * @var string $current_web_root The web root url.
     */
    protected $current_web_root;

    /**
     * @var object $OwpSupportMethods OpenWebPresence support methods.
     */
    protected $OwpSupportMethods;

    /**
     * @var string $default_action Default action.
     */
    public $default_action = "home";

    /**
     * @var string $requested_action The requested action.
     */
    public $requested_action;

    /**
     * @var string $modMethods Mod methods object.
     */
    public $modMethods = null;

    /**
     * @var string $modAvailableMethods Mod methods available.
     */
    public $modAvailableMethods = array();

    /**
     * @var string $THEME Active theme.
     */
    protected $THEME = null;

    /**
     * @var object $ezSqlDB ezSQL Database Object.
     */
    public $userClass;

    /**
     * @var object $frameworkObject Framework Class Object.
     */
    public $frameworkObject;

    public function __construct($root_path, $current_web_root, $PhpConsole)
    {
        /**
         * Set the root path
         */
        $this->root_path = $root_path;

        /**
        * Set the current web root
        */
        $this->current_web_root = $current_web_root;

        /**
         * Set the requested action
         */
        $this->requested_action = (isset($_GET["_route_"])?$_GET["_route_"]:$this->default_action);

        /**
         * Generate $uuid based on OwpSupportMethods->uuid().
         */
        $this->uuid = OwpSupportMethods::uuid();

        /**
         * Set the theme
         */
        $this->theme = $_ENV["THEME"];

        /**
         * Init the debugger
         */
        $this->debug = ((int)$_ENV["ISDEV"]?true:false);

        /**
         * Init the database class
         */
        $this->ezSqlDB = new OwpEzSqlMysql($_ENV["DB_USER"], $_ENV["DB_PASS"], $_ENV["DB_NAME"], $_ENV["DB_HOST"]);
        $this->ezSqlDB->use_disk_cache = false;
        $this->ezSqlDB->cache_queries = false;
        $this->ezSqlDB->hide_errors();

        /**
         * PhpConsole
         */
        $this->PhpConsole = $PhpConsole;

        /**
         * Create the frameworkObject
         */
        $this->frameworkObject = array(
            "frameworkVariables" => array(
                "theme" => $this->theme,
                "current_web_root" => (string)$this->current_web_root,
                "root_path" => (string)$this->root_path,
                "requested_action" => (string)$this->requested_action,
                "uuid" => (string)$this->uuid
            ),
            "ezSqlDB" => (object)$this->ezSqlDB,
            "mod_data" => new OwpMod(),
            "PhpConsole" => (object)$this->PhpConsole
        );

        /**
         * Load the user class
         */
        $this->userClass = new OwpUsers($this->frameworkObject);

        /**
         * Add it to the $frameworkObject array.
         */
        $this->frameworkObject["userClass"] = (object)$this->userClass;

        PC::debug($this->frameworkObject, 'frameworkObject');
    }

    /**
     * __get($name)
     *
     * @method __GET($name) Getter
     * @param $name
     * @return mixed
     */
    public function __get($name)
    {
        if(isset($this->$name)) {
            return $this->$name;
        } else {
            throw new InvalidArgumentException($name." does not exist!");
        }
    }

    /**
     * getFrameworkObject().
     * Return the entire framework object for reference.
     *
     * @method getFrameworkObject() Return the entire framework object for reference.
     * @return array|object
     */
    public function getFrameworkObject() {
        return $this->frameworkObject;
    }

    /**
     * Debug
     *
     * @method void __debugInfo()
     * @access public
     * @return
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo()
    {
        return $this->frameworkObject;
    }
}
