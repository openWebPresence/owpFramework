<?php
ini_set('error_reporting', E_ALL); // or error_reporting(E_ALL);
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');

define("ROOT_PATH", dirname(__FILE__).DIRECTORY_SEPARATOR."tests".DIRECTORY_SEPARATOR);
define("CURRENT_WEB_ROOT", "http://localhost/");
define("DEBUGPASS","test");

include("vendor/autoload.php");

$dotenv = new Dotenv\Dotenv(ROOT_PATH, '.env');
$dotenv->load();