<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com - OwpMessaging_test
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpMessaging_test
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

class OwpMessaging_test extends TestCase
{
    public static $db = null;

    public static $uuid;

    public static $unit_test_email;

    public static $unit_test_name;

    public static $unit_test_password;

    public static function setUpBeforeClass()
    {
        $OwpSupportMethods =  new OwpSupportMethods();
        self::$uuid = $OwpSupportMethods->uuid();
        self::$unit_test_email = "unittest@openwebpresence.com";
    }

    /**
     * @covers OwpMessaging::OwpMessaging_test
     */
    public function testSendEmailViaSMTP()
    {

        $remoteUUID = "Not Found";

        $message_data = array(
            "sender_domain"     => "openwebpresence.com",
            "DKIM_private"    => "",
            "DKIM_selector"   => "",
            "DKIM_passphrase" => "",
            "DKIM_identity"   => "",
            "subject"         => "UnitTEST:" . self::$uuid,
            "message_body"    => "UnitTEST:" . self::$uuid,
            "email_to"        => self::$unit_test_email,
            "email_to_name"   => self::$unit_test_name,
            "email_from"      => self::$unit_test_email,
            "email_from_name" => self::$unit_test_name,
            "reply_to"        => self::$unit_test_email
        );

        $OwpMessaging = new OwpMessaging(ROOT_PATH);
        $OwpMessaging->sendEmailViaSMTP($message_data);

        sleep(2);

        $client = new \GuzzleHttp\Client();
        $res = $client->request('GET', 'http://mailcatch.spamisspam.com/json?uuid=' . self::$uuid);

        $response = json_decode((string)$res->getBody(), true);

        if($response) {
            $remoteUUID = $response["message"]["uuid"];
        }

        $this->assertEquals(
            $remoteUUID,
            self::$uuid
        );
    }
}
