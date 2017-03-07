<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpCms
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
 * This class provides content management functionality.
 */
class OwpCms
{

    /**
     * @var array $settings_data Data storage array
     */
    static protected $settings_data = array();

    /**
     * @var array $ezSqlDB Data storage array
     */
    static protected $ezSqlDB = array();

    /**
     * @var array $srArrayk Macro Keys with double braces
     */
    static protected $srArrayk = array();

    /**
     * @var array $srArrayv Macro values
     */
    static protected $srArrayv = array();

    /**
     * @var array $replacementAssociativeArray Data storage array
     */
    static protected $replacementAssociativeArray = array();


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
        self::$ezSqlDB->cache_queries  = false;
        self::$ezSqlDB->hide_errors();

        self::loadSettings();

    }//end __construct()


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

    }//end __debugInfo()


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

    }//end __get()


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

    }//end __isset()


    /**
     * __set
     *
     * @method mixed __set($itemName, $itemValue) Add mod data by key
     * @access public
     *
     * @param string $itemName  Mod data array key
     * @param mixed  $itemValue Mod data value
     *
     * @return mixed Array value

     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __set($itemName, $itemValue)
    {
        return self::setModDataItem($itemName, $itemValue);

    }//end __set()


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

    }//end __unset()


    /**
     * deleteModDataItem
     *
     * @method mixed deleteModDataItem($itemName) Add mod data by key
     * @access public
     *
     * @param  string $itemName Mod data array key
     * @return mixed Array value
     * @throws Exception SQL exception
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function deleteModDataItem($itemName)
    {
        self::$ezSqlDB->query('BEGIN');
        self::$ezSqlDB->query("DELETE FROM tbl_content WHERE tbl_content.content_name = '".self::$ezSqlDB->escape($itemName)."' LIMIT 1");
        if (self::$ezSqlDB->query('COMMIT') !== false) {
            self::loadSettings();
        } else {
            self::$ezSqlDB->query('ROLLBACK');
            throw new Exception("SQL error while attempting to update ".$itemName.": ".self::$ezSqlDB->last_error);
        }

        return true;

    }//end deleteModDataItem()


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
            throw new InvalidArgumentException("CMS data item ".$itemName." does not exist.", 20);
        }

    }//end getModDataItem()


    /**
     * macroKeyList
     *
     * @method mixed macroKeyList() Returns the macro keys as a list for display.
     * @access public
     *
     * @return string
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function macroKeyList()
    {
        $response = "";
        foreach(self::$replacementAssociativeArray as $rAAk => $rAAv) {
            $response .= "<li>".strtolower($rAAk)."</li>";
        }

        return (string) $response;

    }//end macroKeyList()


    /**
     * macroReplace
     *
     * @method mixed macroReplace($stringData, $replacementAssociativeArray) Provides a macro replacement method the the cms content.
     * @access public
     *
     * @param  string $stringData                  Mod data array key
     * @param  array  $replacementAssociativeArray Replacement data array key
     * @throws InvalidArgumentException Arguments are not the correct data types.
     * @return string
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function macroReplace($stringData, $replacementAssociativeArray)
    {
        if(is_string($stringData) && is_array($replacementAssociativeArray) && array_diff_key($replacementAssociativeArray, array_keys(array_keys($replacementAssociativeArray)))) {
            self::$replacementAssociativeArray = $replacementAssociativeArray;
            self::$srArrayk = array();
            self::$srArrayv = array();
            foreach(self::$replacementAssociativeArray as $rAAk => $rAAv) {
                self::$srArrayk[] = "{{".$rAAk."}}";
                self::$srArrayv[] = $rAAv;
            }

            return str_replace(self::$srArrayk, self::$srArrayv, $stringData);
        }
        else
            {
            throw new InvalidArgumentException("Arguments are not the correct data types.");
        }

    }//end macroReplace()


    /**
     * setModDataItem
     *
     * @method mixed setModDataItem($itemName, $itemValue) Add mod data by key
     * @access public
     *
     * @param  string $itemName  Mod data array key
     * @param  mixed  $itemValue Mod data value
     * @return mixed Array value
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
			REPLACE INTO tbl_content
			SET
				tbl_content.content_name = '".self::$ezSqlDB->escape($itemName)."',
				tbl_content.content_value = '".self::$ezSqlDB->escape(json_encode($itemValue))."',
				tbl_content.content_last_updated = SYSDATE(),
				tbl_content.content_last_updated_by_userID = 0
		"
        );
        if (self::$ezSqlDB->query('COMMIT') !== false) {
            self::loadSettings();
        } else {
            self::$ezSqlDB->query('ROLLBACK');
            throw new Exception("SQL error while attempting to update ".$itemName.": ".self::$ezSqlDB->last_error);
        }

        return self::$settings_data[$itemName];

    }//end setModDataItem()


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
        $tmp = self::$ezSqlDB->get_results("SELECT * FROM tbl_content ORDER BY tbl_content.content_name");
        self::$settings_data = array();
        if($tmp) {
            foreach ($tmp as $t) {
                self::$settings_data[$t->content_name] = json_decode($t->content_value, true);
            }
        }

    }//end loadSettings()


}//end class
