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
    public $requested_action = "home";

    /**
     * @var string $modMethods Mod methods object.
     */
    public $modMethods = null;

    /**
     * @var string $modAvailableMethods Mod methods available.
     */
    public $modAvailableMethods = array();

    /**
     * @var object $mod_data Modifier method data storage.
     */
    protected $mod_data;

    /**
     * @var string $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS Database credentials.
     */
    protected $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS = null;

    /**
     * @var string $THEME Active theme.
     */
    protected $THEME = null;

    /**
     * @var object $ezSqlDB ezSQL Database Object.
     */
    protected $userClass;

    /**
     * @var object $frameworkObject Framework Class Object.
     */
    public $frameworkObject;

    /**
     * Constructor
     *
     * @method void __construct()
     * @access public
     * @param  string $root_path        The app root file path.
     * @param  string $current_web_root The current web root.
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __construct($root_path, $current_web_root)
    {

        /*
		 * Set the root path
		 */
        $this->root_path = $root_path;

        /*
        * Set the root path
        */
        $this->current_web_root = $current_web_root;

        /*
		 * Set the requested action
		 */
        $this->requested_action = (isset($_GET["_route_"])?$_GET["_route_"]:$this->default_action);

        /*
         * Open Web Presence Helper methods
         */
        $this->OwpSupportMethods = new OwpSupportMethods();

        /*
         * Generate $uuid based on OwpSupportMethods->uuid().
         */
        $this->uuid = $this->OwpSupportMethods->uuid();

        /*
		 * Load the environment variables
		 */
        $this->loadEnviroment();

        $this->DB_HOST = $_ENV["DB_HOST"];
        $this->DB_NAME = $_ENV["DB_NAME"];
        $this->DB_USER = $_ENV["DB_USER"];
        $this->DB_PASS = $_ENV["DB_PASS"];
        $this->THEME = $_ENV["THEME"];

        /*
		 * Init the debugger
		 */
        $this->debug = ((int)$_ENV["ISDEV"]?true:false);

        /*
		 * Init the database class
		 */
        $this->ezSqlDB = new OwpEzSqlMysql($this->DB_USER, $this->DB_PASS, $this->DB_NAME, $this->DB_HOST);
        $this->ezSqlDB->use_disk_cache = false;
        $this->ezSqlDB->cache_queries = false;
        $this->ezSqlDB->hide_errors();

        /*
         * Create an object reference to pass to user defined class libraries.
         */
        $this->frameworkObject = array(
            "ezSqlDB" => (object)$this->ezSqlDB,
            "mod_data" => (array)$this->mod_data,
            "current_web_root" => (string)$this->current_web_root,
            "root_path" => (string)$this->root_path,
            "OwpSupportMethods" => (object)$this->OwpSupportMethods,
            "requested_action" => (string)$this->requested_action,
            "uuid" => (string)$this->uuid,
            "SqueakyMindsPhpHelper" => new SqueakyMindsPhpHelper()
        );

        /*
         * Load the user class
         */
        $this->userClass = new OwpUsers($this->frameworkObject);

        /*
         * Add it to the $frameworkObject array.
         */
        $this->frameworkObject["userClass"] = $this->userClass;

        /*
         * Dynamic OwpCommon include
         */
        $modCommonFileLocation = $this->root_path . join(DIRECTORY_SEPARATOR, array("app","themes",$this->THEME,"lib","OwpCommon.inc.php"));
        include $modCommonFileLocation;
        $this->OwpCommon = new OwpCommon($this->frameworkObject);

        if($this->requested_action != "ajax") {
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
            if(class_exists($modFileIncludeName)) {
                $this->modAvailableMethods = get_class_methods($this->modMethods);
            }
        }
    }

    /**
     * Debug
     *
     * @method void __debugInfo()
     * @access public
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo() 
    {
        return [
            "requested_action" => $this->requested_action,
            "default_action" => $this->default_action,
            "MySQL_Errors" => $this->ezSqlDB->captured_errors,
            "UserClass" => $this->userClass,
            "root_path" => $this->root_path,
            "current_web_root" => $this->current_web_root,
            "mod_data" => $this->mod_data,
            "theme" => $this->THEME,
            "_ENV" => $_ENV,
            "_POST" => $_POST,
            "_GET" => $_GET,
            "_SESSION" => $_SESSION,
            "_SERVER" => $_SERVER,
            "uuid" => $this->uuid,
        ];
    }

    /**
     * loadEnviroment()
     *
     * @method void loadEnviroment() Establish the app environment using the Dotenv library.
     * @access private
     *
     * @uses https://packagist.org/packages/vlucas/phpdotenv Dotenv\Dotenv Loads environment variables from .env to getenv(), $_ENV and $_SERVER automagically. This is a PHP version of the original Ruby dotenv.
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function loadEnviroment() 
    {
        $dotenv = new Dotenv\Dotenv($this->root_path, '.env');
        $dotenv->load();
        // database
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'])->notEmpty();
        // theme
        $dotenv->required(['THEME'])->notEmpty();
        // phpmailer
        $dotenv->required(['smtp_hostname', 'smtp_auth', 'smtp_username', 'smtp_password', 'smtp_port'])->notEmpty();
        $dotenv->required(['smtp_secure'])->allowedValues(['ssl', 'tls', 'none']);
        $dotenv->required(['DKIM_domain'])->notEmpty();
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
     * loadNav()
     *
     * @method void loadNav() Loads the nav based on the template setting.
     * @access protected
     * @uses   $this->loadTemplate()
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
                $this->mod_data = $tmpClass->process();
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
            $this->OwpAjaxUdf = new OwpAjaxUdf();

            if(class_exists("OwpAjaxUdf")) {
                call_user_func(array("OwpAjaxUdf","processAction"), $this->frameworkObject);
            } else {
                throw new Exception("User class OwpAjaxUdf() does not exist.", 911);
            }

            break;
        }
    }
}
