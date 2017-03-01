<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpSettings
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
 * This class provides settings data management functionality.
 */
class OwpSettings
{

    /**
     * @var array $settings_data Data storage array
     */
    static protected $settings_data = array();

    /**
     * @var array $ezSqlDB Data storage array
     */
    static public $ezSqlDB = array();

    /**
     * Constructor.
     *
     * @method mixed __construct()
     * @access public
     */
    function __construct()
    {
        $dotenv = new Dotenv\Dotenv(ROOT_PATH, '.env');
        $dotenv->load();

        $dotenv->required(['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS']);

        /*
         * Init the database class
         */
        self::$ezSqlDB = new ezSQL_mysql($_ENV["DB_USER"], $_ENV["DB_PASS"], $_ENV["DB_NAME"], $_ENV["DB_HOST"]);
        self::$ezSqlDB->use_disk_cache = false;
        self::$ezSqlDB->cache_queries = false;
        self::$ezSqlDB->hide_errors();

        self::loadSettings();
    }

    /**
     * __debugInfo
     *
     * @method mixed __debugInfo()
     * @access public
     *
     * @return mixed settings_data
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo()
    {
        return [
            'settings_data' => self::$settings_data,
        ];
    }

    /**
     * __get
     *
     * @method mixed __get($itemName) Add mod data by key
     * @access public
     *
     * @param string $itemName Mod data array key
     *
     * @return mixed Array value
     *
     * @throws InvalidArgumentException Mod data key does not exist, code 20
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __get($itemName)
    {
        return self::getModDataItem($itemName);
    }

    /**
     * __isset
     *
     * @method mixed __isset($itemName) Add mod data by key
     * @access public
     *
     * @param string $itemName Mod data array key
     *
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __isset($itemName)
    {
        return isset(self::$settings_data[$itemName]);
    }

    /**
     * __set
     *
     * @method mixed __set($itemName, $itemValue) Add mod data by key
     * @access public
     *
     * @param string $itemName  Mod data array key
     * @param mixed  $itemValue Mod data value
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __set($itemName, $itemValue)
    {
        self::setModDataItem($itemName, $itemValue);
    }

    /**
     * __unset
     *
     * @method mixed __unset($itemName) Add mod data by key
     * @access public
     *
     * @param string $itemName Mod data array key
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __unset($itemName)
    {
        if(isset(self::$settings_data[$itemName])) {
            self::deleteModDataItem($itemName);
        }
    }

    /**
     * deleteModDataItem
     *
     * @method mixed deleteModDataItem($itemName) Add mod data by key
     * @access public
     *
     * @param  string $itemName Mod data array key
     * @return boolean Array value
     * @throws Exception SQL exception
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function deleteModDataItem($itemName)
    {
        self::$ezSqlDB->query('BEGIN');
        self::$ezSqlDB->query("DELETE FROM tbl_settings WHERE tbl_settings.setting_name = '" . self::$ezSqlDB->escape($itemName) . "' LIMIT 1");
        if (self::$ezSqlDB->query('COMMIT') !== false) {
            self::loadSettings();
        } else {
            self::$ezSqlDB->query('ROLLBACK');
            throw new Exception("SQL error while attempting to update " . $itemName . ": " . self::$ezSqlDB->last_error);
        }

        return true;
    }

    /**
     * getModDataItem
     *
     * @method mixed getModDataItem($itemName) Add mod data by key
     * @access public
     *
     * @param string $itemName Mod data array key
     *
     * @return mixed Array value
     *
     * @throws InvalidArgumentException Mod data key does not exist, code 20
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function getModDataItem($itemName)
    {
        if(isset(self::$settings_data[$itemName])) {
            return self::$settings_data[$itemName];
        } else {
            throw new InvalidArgumentException("Data item " . $itemName . " does not exist.", 20);
        }
    }

    /**
     * setModDataItem
     *
     * @method mixed setModDataItem($itemName, $itemValue) Add mod data by key
     * @access public
     *
     * @param  string $itemName  Mod data array key
     * @param  mixed  $itemValue Mod data value
     * @throws Exception SQL exception
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function setModDataItem($itemName, $itemValue)
    {
        self::$ezSqlDB->query('BEGIN');
        self::$ezSqlDB->query(
            "
			REPLACE INTO tbl_settings
			SET
				tbl_settings.setting_name = '" . self::$ezSqlDB->escape($itemName) . "',
				tbl_settings.setting_value = '" . self::$ezSqlDB->escape(json_encode($itemValue)) . "',
				tbl_settings.setting_last_updated = SYSDATE(),
				tbl_settings.setting_last_updated_by_userID = 0
		"
        );
        if (self::$ezSqlDB->query('COMMIT') !== false) {
            self::loadSettings();
        } else {
            self::$ezSqlDB->query('ROLLBACK');
            throw new Exception("SQL error while attempting to update " . $itemName . ": " . self::$ezSqlDB->last_error);
        }
    }

    /**
     * loadSettings
     *
     * @method mixed loadSettings() Add mod data by key
     * @access private
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static private function loadSettings()
    {
        $tmp  = self::$ezSqlDB->get_results("SELECT * FROM tbl_settings ORDER BY tbl_settings.setting_name");
        self::$settings_data = array();
        if($tmp) {
            foreach ($tmp as $t) {
                self::$settings_data[$t->setting_name] = json_decode($t->setting_value, true);
            }
        }
    }
}
