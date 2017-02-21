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


class OwpBaseFramework
{


    /**
     * @var array $debugging Debugging array
     */
    public $debugging = array();

    /**
     * @var boolean $debug Is debugging enabled by default
     */
    public $debug = false;

    /**
     * @var int $userID OpenWebPresence support methods
     */
    public $userID = 0;

    /**
     * @var object $ezSqlDB ezSQL Database Object
     */
    protected $ezSqlDB;

    /**
     * @var object $firephp FirePHP debugging libray
     */
    protected $firephp;

    /**
     * @var string $current_web_root The web root url
     */
    protected $current_web_root;

    /**
     * @var object $owp_SupportMethods OpenWebPresence support methods
     */
    protected $owp_SupportMethods;

    /**
     * @var string $default_action Default action
     */
    public $default_action = "home";

    /**
     * @var string $requested_action Default action
     */
    public $requested_action = "home";

    /**
     * @var object $mod_data Modifier method data storage
     */
    protected $mod_data;

    /**
     * @var string $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS Database credentials
     */
    protected $DB_HOST, $DB_NAME, $DB_USER, $DB_PASS = null;

    /**
     * @var string $THEME Active theme
     */
    protected $THEME = null;

    /**
     * @var object $ezSqlDB ezSQL Database Object
     */
    protected $userClass;


    /**
     * Constructor
     *
     * @method void __construct()
     * @access public
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __construct() 
    {
        /*
		 * Set the requested action
		 */
        $this->requested_action = (isset($_GET["_route_"])?$_GET["_route_"]:$this->default_action);

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

        $this->firephp = FirePHP::getInstance(true);
        $this->firephp->setEnabled((bool)$this->debug);

        if((bool)$this->debug) {
            $this->firephp->registerErrorHandler($throwErrorExceptions = true);
            $this->firephp->registerExceptionHandler();
            $this->firephp->registerAssertionHandler($convertAssertionErrorsToExceptions = true, $throwAssertionExceptions = false);

            $this->firephp->group('Initial State');
            $this->firephp->log($_GET, '_GET');
            $this->firephp->log($_POST, '_POST');
            $this->firephp->log($_SESSION, '_SESSION');
            $this->firephp->log($_SERVER, '_SERVER');
            $this->firephp->log($_ENV, '_ENV');
            $this->firephp->log(session_id(), 'session_id');
            $this->firephp->log(array($this->DB_USER, $this->DB_PASS, $this->DB_NAME, $this->DB_HOST), 'DB');
            $this->firephp->groupEnd();
        }

        $this->firephp->group('Process State');
        /*
		 * Init the database class
		 */
        $this->ezSqlDB = new owp_EzSql_mysql($this->DB_USER, $this->DB_PASS, $this->DB_NAME, $this->DB_HOST);
        $this->ezSqlDB->use_disk_cache = false;
        $this->ezSqlDB->cache_queries = false;
        $this->ezSqlDB->hide_errors();


        /*
		 * Process the request
		 */
        $this->processAction();
        $this->firephp->groupEnd();

        $this->firephp->group('Completion State');
        $this->firephp->log($this->debugging, 'Debugging');
        $this->firephp->log($this->ezSqlDB->captured_errors, 'MySQL Errors');

        $this->firephp->groupEnd();

        $this->userClass = new OwpUsers($this->ezSqlDB, $this->firephp, CURRENT_WEB_ROOT);
    }

    /**
     * loadEnviroment
     *
     * @method void __construct()
     * @access public
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function loadEnviroment() 
    {
        $dotenv = new Dotenv\Dotenv(ROOT_PATH, '.env');
        $dotenv->load();
        // database
        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS'])->notEmpty();
        // theme
        $dotenv->required(['THEME'])->notEmpty();
        // phpmailer
        $dotenv->required(['smtp_hostname', 'smtp_auth', 'smtp_username', 'smtp_password', 'smtp_port'])->notEmpty();
        $dotenv->required(['smtp_secure'])->allowedValues(['ssl', 'tls', 'none']);
        $dotenv->required(['DKIM_domain', 'DKIM_private', 'DKIM_selector', 'DKIM_passphrase','DKIM_identity'])->notEmpty();
    }


    private function loadFooter()
    {
        $this->loadTemplate("footer", "common");
    }


    private function loadHeader()
    {
        $this->loadTemplate("header", "common");
    }


    private function loadNav()
    {
        $this->loadTemplate("nav", "common");
    }



    private function loadTemplate($template_name,$sub_dir = "pages")
    {
        $template = array();
        $template["theme"] = ROOT_PATH . join(DIRECTORY_SEPARATOR, array('app', 'themes', $_ENV["THEME"], $sub_dir, $template_name . ".inc.php"));
        $template["default"] = ROOT_PATH . join(DIRECTORY_SEPARATOR, array('app', 'themes', 'default', $sub_dir, $template_name . ".inc.php"));

        foreach($template as $tk => $tv) {
            if(file_exists($tv)) {
                $this->debugging["templates"][] = array($tk,$tv);
                include $tv;
                return true;
            }
        }

        return false;
    }

    protected function mod()
    {
        $class_name = "owp_mod_" . $this->requested_action;

        $mods["theme_functions"] = ROOT_PATH . join(DIRECTORY_SEPARATOR, array('app', 'themes', $_ENV["THEME"], "owpFunctions.inc.php"));
        if(file_exists($mods["theme_functions"])) {
            include $mods["theme_functions"];
        }

        $mods = array();
        $mods["theme"] = ROOT_PATH . join(DIRECTORY_SEPARATOR, array('app', 'themes', $_ENV["THEME"], 'mods', 'pages', $class_name . ".inc.php"));
        $mods["default"] = ROOT_PATH . join(DIRECTORY_SEPARATOR, array('app', 'themes', 'default', 'mods', 'pages', $class_name . ".inc.php"));

        foreach($mods as $tk => $tv) {
            if(file_exists($tv)) {
                $this->debugging["mods"][] = array($tk,$tv);
                include $tv;
                $tmpClass = new $class_name($this->firephp, $this->ezSqlDB);
                $this->mod_data = $tmpClass->process();
            }
        }

        $this->firephp->log($mods, 'framework->mod()');
    }


    private function processAction() 
    {
        $this->mod();
        $this->loadHeader();
        $this->loadNav();
        $this->loadTemplate($this->requested_action);
        $this->loadFooter();
    }
}
