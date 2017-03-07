<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpStdObject
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
 * stdObject class to create classes.
 */
class OwpStdObject
{


    /**
     * __construct
     *
     * @method __construct($arguments)
     * @access public
     * @param  array $arguments Object arguments
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    public function __construct(array $arguments = array())
    {
        if (!empty($arguments)) {
            foreach ($arguments as $property => $argument) {
                $this->{$property} = $argument;
            }
        }

    }//end __construct()


    /**
     * __call
     *
     * @method __call($method, $arguments)
     * @access public
     * @param  object $method    Object arguments
     * @param  mixed  $arguments Method arguments
     * @return mixed
     * @throws Exception Fatal error: Call to undefined method stdObject
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    public function __call($method, $arguments)
    {
        $arguments = array_merge(array("stdObject" => $this), $arguments);
        if (isset($this->{$method}) && is_callable($this->{$method})) {
            return call_user_func_array($this->{$method}, $arguments);
        } else {
            throw new Exception("Fatal error: Call to undefined method stdObject::{$method}()");
        }

    }//end __call()


}//end class
