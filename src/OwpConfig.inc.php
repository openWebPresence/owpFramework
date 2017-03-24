<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpConfig
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
 * This class provides configuration information.
 */
class OwpConfig
{

    /**
     * GetConfigItem
     *
     * @method mixed GetConfigItem($subjectName,$itemName) Get configuration setting
     * @access public
     *
     * @param string $subjectName Subject Data Array Name
     * @param string $itemName    Subject Data Array Key Name
     *
     * @return mixed Array value
     *
     * @throws InvalidArgumentException Config data does not exist, code 20
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function GetConfigItem($subjectName,$itemName)
    {
        $includePath = ROOT_PATH."config".DIRECTORY_SEPARATOR.$subjectName.".php";

        if(!file_exists($includePath)) {
            throw new InvalidArgumentException($subjectName." include not found.", 20);
        }

        include $includePath;

        if(!empty($$subjectName) && array_key_exists($itemName, $$subjectName)) {
            $response = $$subjectName;
            return $response[$itemName];
        } else {
            throw new InvalidArgumentException($subjectName."->".$itemName." does not exist.", 20);
        }

    }//end GetConfigItem()
}