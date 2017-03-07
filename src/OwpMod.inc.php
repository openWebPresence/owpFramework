<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpMod
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
 * This class provides mod data management functionality. (Non session based global storage.)
 */
class OwpMod
{

    /**
     * @var array $mod_data Data storage array
     */
    private  $mod_data = array();


    /**
     * __debugInfo
     *
     * @method mixed __debugInfo()
     * @access public
     *
     * @return mixed mod_data
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo()
    {
        return [
                'mod_data' => $this->mod_data,
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
        return $this->getModDataItem($itemName);

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
        PC::debug($itemName, 'OwpMod->__isset()');
        return isset($this->mod_data[$itemName]);

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
        return $this->setModDataItem($itemName, $itemValue);

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
        if(isset($this->mod_data[$itemName])) {
            unset($this->mod_data[$itemName]);
        }

    }//end __unset()


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
    public function getModDataItem($itemName)
    {
        PC::debug(array("itemName" => $itemName), 'OwpMod->getModDataItem()');
        if(isset($this->mod_data[$itemName])) {
            return $this->mod_data[$itemName];
        } else {
            throw new InvalidArgumentException("Mod data item ".$itemName." does not exist.", 20);
        }

    }//end getModDataItem()


    /**
     * setModDataItem
     *
     * @method mixed setModDataItem($itemName, $itemValue) Add mod data by key
     * @access public
     *
     * @param string $itemName  Mod data array key
     * @param mixed  $itemValue Mod data value
     *
     * @return mixed Array value

     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function setModDataItem($itemName, $itemValue)
    {
        PC::debug(array("itemName" => $itemName, "itemValue" => $itemValue), 'OwpMod->setModDataItem()');
        $this->mod_data[$itemName] = $itemValue;
        return $this->mod_data[$itemName];

    }//end setModDataItem()


}//end class
