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

/**
 * Provides password validation.
 */
class OwpPasswordValidator
{
    /**
     * @var string $tooShortMessage TooShortMessage
     */
    static public $tooShortMessage = 'Your password must be at least {{minlength}} characters long.';

    /**
     * @var string $tooLongMessage TooLongMessage
     */
    static public $tooLongMessage = 'Your password must not exceed {{maxlength}} characters.';

    /**
     * @var string $missingLettersMessage MissingLettersMessage
     */
    static public $missingLettersMessage = 'Your password must include at least one letter.';

    /**
     * @var string $requireCaseDiffMessage RequireCaseDiffMessage
     */
    static public $requireCaseDiffMessage = 'Your password must include both upper and lower case letters.';

    /**
     * @var string $missingNumbersMessage MissingNumbersMessage
     */
    static public $missingNumbersMessage = 'Your password must include at least one digit.';

    /**
     * @var string $missingNonAlphanumericMessage MissingNonAlphanumericMessage
     */
    static public $missingNonAlphanumericMessage = 'Your password must include at least one non-alphanumeric character.';

    /**
     * @var string $minLength MinLength
     */
    static public $minLength = 5;

    /**
     * @var string $maxLength MaxLength
     */
    static public $maxLength = 0;

    /**
     * @var string $requireLetters RequireLetters
     */
    static public $requireLetters = true;

    /**
     * @var string $requireCaseDiff RequireCaseDiff
     */
    static public $requireCaseDiff = false;

    /**
     * @var string $requireNumbers RequireNumbers
     */
    static public $requireNumbers = false;

    /**
     * @var string $requireNonAlphanumeric RequireNonAlphanumeric
     */
    static public $requireNonAlphanumeric = false;

    /**
     * @var string $charset Allowed Characterset
     */
    static public $charset = 'UTF-8';

    /**
     * @var string $violations Violation array
     */
    static public $violations = array();

    /**
     * @var string $constraint Validation constraints
     */
    static public $constraint;


    /**
     * validate
     *
     * @method validate($value, $constraint)
     * @access public
     * @param  string $value      Password value
     * @param  string $constraint Constraints
     * @return mixed Violation array or false if no violations
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static public function validate($value, $constraint)
    {
        self::$constraint = $constraint;

        $stringValue = (string) $value;
        if (function_exists('grapheme_strlen') && 'UTF-8' === self::$constraint->charset) {
            $length = grapheme_strlen($stringValue);
        } else if (function_exists('mb_strlen')) {
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

        return (self::$violations ? self::$violations : false);

    }//end validate()


    /**
     * addViolation
     *
     * @method addViolation($violation)
     * @access private
     * @param  string $violation Violation to record
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static private function addViolation($violation)
    {
        self::$violations[] = str_replace(array("{{minlength}}", "{{maxlength}}"), array(self::$constraint->minLength, self::$constraint->maxLength), $violation);

    }//end addViolation()


}//end class
