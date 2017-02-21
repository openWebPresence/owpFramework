<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpEzSqlMysqli
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

class OwpEzSqlMysqli extends ezSQL_mysqli
{

    /**
     * MySQLFirephp()
     *
     * @method mixed MySQLFirephp($firephp, $title) Integrate FirePHP debugging into the EzSQL MySQLi obstraction layer.
     * @access public
     *
     * @param object $firephp Traditional FirePHPCore library for sending PHP variables to the browser.
     * @param string $title   Optional title.
     *
     * @uses firephp::table() FirePHPCore table method.
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function MySQLFirephp($firephp, $title = "ezSql Debug") 
    {
            
        $table   = array();
        $table[] = array('Item', 'Detail');
        if (! $this->debug_called ) {
            $table[] = array('EZSQL_VERSION', EZSQL_VERSION);
        }
        if ($this->last_error ) {
            $table[] = array('Last Error', $this->last_error);
        }
        $table[] = array('Query [' . $this->num_queries . ']', $this->last_query);
        if ($this->col_info ) {
            $table[] = array('Col Info', $this->col_info);
        }
        $table[] = array('Last Result', ($this->last_result?$this->get_results(null, ARRAY_A):"No Results"));
        $firephp->table($title, $table);

        $this->debug_called = true;
    }

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
        if ($this->last_error) {
            return $this->last_error;
        } else {
            return null;
        }
    }
}
