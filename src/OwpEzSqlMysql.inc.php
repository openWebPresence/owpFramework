<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpEzSqlMysql
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
 * This class integrates FirePHP debugging into the EzSQL MySQL (not MySQLi) abstraction layer.
 */
class OwpEzSqlMysql extends OwpDBMySQLi
{


    /**
     * @var object $ezSqlDB ezSQL Database Object
     */
    static private $ezSqlDB;


    /**
     * debugPhpConsole
     *
     * @method void debugPhpConsole()
     * @access public
     * @param  string $title Debug Title
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function debugPhpConsole($title = "OwpEzSqlMysql.ezSQL_mysql.debugPhpConsole") 
    {
        $debug = array();

        if ($this->last_error ) {
            $debug["last_error"] = $this->last_error;
        }

        if ($this->last_query ) {
            $debug["last_query"] = $this->last_query;
        }

        if($this->captured_errors) {
            $debug["captured_errors"] = $this->captured_errors;
        }

        if(!empty($this->last_result)) {
            $debug["last_result"] = $this->get_results(null, ARRAY_N);
        }

        PC::debug($debug, $title);
    }//end debugPhpConsole()


    /**
     * Debug
     *
     * @method void __debugInfo()
     * @access public
     * @uses   $this->userData()
     * @uses   $this->userData()
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo()
    {
        return [
                "Last Error"    => $this->last_error,
                "Last Query"    => $this->last_query,
                "Last Result"   => (!empty($this->last_result) ? $this->get_results(null, ARRAY_A) : "No Results"),
               ];

    }//end __debugInfo()


    /**
     * MySQLFirephp()
     *
     * @method mixed MySQLFirephp($title) Integrate FirePHP debugging into the EzSQL MySQLi obstraction layer.
     * @access public
     *
     * @param string $title Optional title.
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function MySQLFirephp($title = "ezSql Debug")
    {

        $table   = array();
        $table[] = array(
                    'Item',
                    'Detail',
                   );

        if ($this->last_error) {
            $table[] = array(
                        'Last Error',
                        $this->last_error,
                       );
        }

        $table[] = array(
                    'Query',
                    $this->last_query,
                   );
        if ($this->col_info) {
            $table[] = array(
                        'Col Info',
                        $this->col_info,
                       );
        }

        $table[] = array(
                    'Last Result',
                    (!empty($this->last_result) ? $this->get_results(null, ARRAY_A) : "No Results"),
                   );

        PC::debug($table, $title);

    }//end MySQLFirephp()


    /**
     * MySQLFirephpGetLastMysqlError()
     *
     * @method mixed MySQLFirephpGetLastMysqlError() Integrate FirePHP debugging into the EzSQL MySQLi obstraction layer.
     * @access public
     *
     * @return mixed Last recorded MySQL Error
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function MySQLFirephpGetLastMysqlError()
    {
        if (!empty($this->last_error)) {
            return $this->last_error;
        } else {
            return null;
        }

    }//end MySQLFirephpGetLastMysqlError()


    /**
     * executeSqlFile
     *
     * @method void executeSqlFile($filename) Execute SQL text file
     * @param  $filename
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function executeSqlFile($filename)
    {
        self::initDb();
        $query_sql = self::$ezSqlDB->escape(file_get_contents($filename));
        return self::$ezSqlDB->query($query_sql);
    }//end executeSqlFile()


    /**
     * initDb
     *
     * @method initDb()
     * @access public
     * @return object
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function initDb()
    {
        if(!self::$ezSqlDB) {
            self::$ezSqlDB = new OwpEzSqlMysql($_ENV["DB_USER"], $_ENV["DB_PASS"], $_ENV["DB_NAME"], $_ENV["DB_HOST"]);
            self::$ezSqlDB->use_disk_cache = false;
            self::$ezSqlDB->cache_queries  = false;
            self::$ezSqlDB->hide_errors();
        }

        return self::$ezSqlDB;

    }//end initDb()

}//end class
