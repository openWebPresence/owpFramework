<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpSupportMethods
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
 * This class provides various helper methods which can be used in any php development projects.
 */
class OwpSupportMethods
{
    /**
     * uuid
     *
     * @method uuid()
     * @access public
     * @return string
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static public function uuid()
    {
        return md5(uniqid(rand()+MicroTime(), 1));
    }


    /**
     * randomPasswordAlphaNum
     *
     * @method randomPasswordAlphaNum()
     * @access public
     * @return string
     * @param  int $length Random password length.
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static public function randomPasswordAlphaNum($length)
    {
        $alphabets = range('A', 'Z');
        $alphabets_lower = range('a', 'z');
        $numbers = range('0', '9');
        $final_array = array_merge($alphabets, $numbers, $alphabets_lower);

        $password = '';

        while($length--) {
            $key = array_rand($final_array);
            $password .= $final_array[$key];
        }

        return (string)$password;
    }


    /**
     * GetUserIP
     *
     * @method GetUserIP()
     * @access public
     * @return string
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static public function GetUserIP() 
    {
        if(isset($_SERVER) && isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return "127.0.0.1";
        }
    }


    /**
     * includeIfExists
     *
     * @method includeIfExists($file) Includes file if it exists.
     * @access public
     * @param  string $file File to include.
     * @return mixed
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static function includeIfExists($file)
    {
        if (file_exists($file)) {
            return include $file;
        }
    }
}
