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
        return md5(uniqid((rand() + MicroTime()), 1));

    }//end uuid()


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
        $alphabets       = range('A', 'Z');
        $alphabets_lower = range('a', 'z');
        $numbers         = range('0', '9');
        $final_array     = array_merge($alphabets, $numbers, $alphabets_lower);

        $password = '';

        while($length--) {
            $key       = array_rand($final_array);
            $password .= $final_array[$key];
        }

        return (string) $password;

    }//end randomPasswordAlphaNum()


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

    }//end GetUserIP()


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

    }//end includeIfExists()


    /**
     * filterAction
     *
     * @method filterAction($action) Includes file if it exists.
     * @access public
     * @param  $action
     * @return string
     */
    static function filterAction($action)
    {
        return (string) preg_replace("/[^A-Za-z0-9_-]/", "", $action);

    }//end filterAction()

    /**
     * file_get_php_classes
     *
     * @method file_get_php_classes($filepath) Get classes from a file.
     * @param $filepath
     * @return array
     */
    static function file_get_php_classes($filepath) {
        $php_code = file_get_contents($filepath);
        $classes = OwpSupportMethods::get_php_classes($php_code);
        return $classes;
    }

    /**
     * get_php_classes
     *
     * @method get_php_classes($php_code) Get classes froma string.
     * @param $php_code
     * @return array
     */
    static function get_php_classes($php_code) {
        $classes = array();
        $tokens = token_get_all($php_code);
        $count = count($tokens);
        for ($i = 2; $i < $count; $i++) {
            if (   $tokens[$i - 2][0] === T_CLASS
                && $tokens[$i - 1][0] === T_WHITESPACE
                && $tokens[$i][0] === T_STRING) {
                $class_name = $tokens[$i][1];
                $classes[] = $class_name;
            }
        }
        return $classes;
    }
}//end class
