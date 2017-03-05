<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com - OwpCms_test
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpCms_test
 * @author    Brian Tafoya <btafoya@briantafoya.com>
 * @version   1.0
 * @license   MIT
 * @license   https://opensource.org/licenses/MIT The MIT License
 * @category  OpenWebPresence Support Library
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

use PHPUnit\Framework\TestCase;

class OwpCms_test extends TestCase
{

    /**
     * @covers OwpCms::__set
     * @covers OwpCms::__get
     */
    public function testSetGet()
    {
        $OwpCms =  new OwpCms();
        $OwpCms->WhatDoISay = array("HelloILoveYou");

        $this->assertEquals(
            $OwpCms->WhatDoISay,
            array("HelloILoveYou")
        );
    }

    /**
     * @expectedException InvalidArgumentException
     * @covers OwpCms::__set
     * @covers OwpCms::__get
     * @depends testSetGet
     */
    public function testDeleteGet()
    {
        $OwpCms =  new OwpCms();
        unset($OwpCms->WhatDoISay);
        $getMe = $OwpCms->WhatDoISay;
    }

    /**
     * @covers OwpCms::__set
     * @covers OwpCms::__get
     * @covers OwpCms::macroReplace()
     * @depends testSetGet
     */
    public function testMacroReplace()
    {
        $testArray = array("owpVariable" => "test", "phpUnit" => "Rocks");
        $OwpCms =  new OwpCms();
        $OwpCms->MyTestMacro = "The is my {{owpVariable}} macro! Sweet {{phpUnit}}";
        $getMe = $OwpCms->macroReplace($OwpCms->MyTestMacro, $testArray);
        unset($OwpCms->MyTestMacro);

        $this->assertEquals(
            "The is my test macro! Sweet Rocks",
            $getMe
        );
    }
}
