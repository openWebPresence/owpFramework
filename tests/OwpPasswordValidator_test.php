<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com - OwpPasswordValidator_test
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpPasswordValidator_test
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

class OwpPasswordValidator_test extends TestCase
{

    /**
     * @covers OwpPasswordValidator::validate
     */
    public function testValidatePass()
    {

        $passwordConstraints = (object) [
            'minLength' => 5,
            'maxLength' => 15,
            'requireLetters' => true,
            'requireCaseDiff' => true,
            'requireNumbers' => true,
            'requireNonAlphanumeric' => true,
            'charset' => 'UTF-8',
        ];

        $value = "Th1s1SMyP$$";
        $OwpPasswordValidator =  new OwpPasswordValidator();
        $results = $OwpPasswordValidator->validate($value, $passwordConstraints);

        $this->assertEquals(
            $results,
            false
        );
    }

    /**
     * @covers OwpPasswordValidator::validate
     */
    public function testValidatePass2()
    {

        $passwordConstraints = (object) [
            'minLength' => 5,
            'maxLength' => 15,
            'requireLetters' => true,
            'requireCaseDiff' => true,
            'requireNumbers' => true,
            'requireNonAlphanumeric' => false,
            'charset' => 'UTF-8',
        ];

        $value = "Th1s1SMyPdd";
        $OwpPasswordValidator =  new OwpPasswordValidator();
        $results = $OwpPasswordValidator->validate($value, $passwordConstraints);

        $this->assertEquals(
            $results,
            false
        );
    }
}
