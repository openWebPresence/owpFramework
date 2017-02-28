<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpPasswordValidator
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

/*
 * Provides password validation.
 */
class OwpPasswordValidator
{
    static public $tooShortMessage = 'Your password must be at least {{minlength}} characters long.';
    static public $tooLongMessage = 'Your password must not exceed {{maxlength}} characters.';
    static public $missingLettersMessage = 'Your password must include at least one letter.';
    static public $requireCaseDiffMessage = 'Your password must include both upper and lower case letters.';
    static public $missingNumbersMessage = 'Your password must include at least one digit.';
    static public $missingNonAlphanumericMessage = 'Your password must include at least one non-alphanumeric character.';

    static public $minLength = 5;
    static public $maxLength = 0;
    static public $requireLetters = true;
    static public $requireCaseDiff = false;
    static public $requireNumbers = false;
    static public $requireNonAlphanumeric = false;
    static public $charset = 'UTF-8';

    static public $violations = array();
    static public $constraint;

    static public function validate($value, $constraint)
    {
        self::$constraint = $constraint;

        if (null === $value || '' === $value) {
            return false;
        }
        $stringValue = (string) $value;
        if (function_exists('grapheme_strlen') && 'UTF-8' ===  self::$constraint->charset) {
            $length = grapheme_strlen($stringValue);
        } elseif (function_exists('mb_strlen')) {
            $length = mb_strlen($stringValue,  self::$constraint->charset);
        } else {
            $length = strlen($stringValue);
        }
        if(self::$constraint->minLength > 0 && ($length < self::$constraint->minLength))
            self::addViolation(self::$tooShortMessage);
        if($constraint->maxLength > 0 && ($length > self::$constraint->maxLength))
            self::addViolation(self::$tooLongMessage);
        if($constraint->requireLetters && !preg_match('/\pL/', $stringValue))
            self::addViolation(self::$missingLettersMessage);
        if($constraint->requireCaseDiff && !preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/', $stringValue))
            self::addViolation(self::$requireCaseDiffMessage);
        if($constraint->requireNumbers && !preg_match('/\pN/', $stringValue))
            self::addViolation(self::$missingNumbersMessage);
        if($constraint->requireNonAlphanumeric && ctype_alnum($stringValue))
            self::addViolation(self::$missingNonAlphanumericMessage);

        return (self::$violations?self::$violations:false);
    }
    
    static public function addViolation($violation)
    {
        self::$violations[] = str_replace(array("{{minlength}}","{{maxlength}}"),array(self::$constraint->minLength,self::$constraint->maxLength,),$violation);
    }
}