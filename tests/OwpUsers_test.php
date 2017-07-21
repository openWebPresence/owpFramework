<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com - OwpUsers_test
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpUsers_test
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

PhpConsole\Helper::register();

if (!isset($_SESSION)) $_SESSION = array();

class OwpUsers_test extends TestCase
{
    public static $db = null;
    public static $passwd = null;
    public static $passwdSecond = null;
    public static $uuid = null;
    public static $createUserTestData = null;
    public static $getUserTestData = null;
    public static $owpUsers;
    public static $current_web_root;
    public static $userID;
    public static $root_path;
    public static $lostPassUUID = null;
    public static $shared_session = array();
    public static $frameworkObject = array();

    public static function setUpBeforeClass()
    {

        /*
		 * Init the database class
		 */
        self::$db = OwpDb::init(getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'), getenv('DB_HOST'));

        self::$current_web_root = "http://openwebpresence.com/";
        self::$root_path = dirname(__FILE__) . DIRECTORY_SEPARATOR;

        $owp_SupportMethods = new OwpSupportMethods();
        self::$uuid = $owp_SupportMethods->uuid();

        $requested_action = "home";


        self::$passwd = $owp_SupportMethods->randomPasswordAlphaNum(10);
        self::$passwdSecond = $owp_SupportMethods->randomPasswordAlphaNum(10);

        self::$createUserTestData = array(
            "email" => "phpunit@openwebpresence.com",
            "first_name" => "phpUnit",
            "last_name" => "Test",
            "passwd" => self::$passwd,
            "statusID" => 1,
            "welcome_email_sent" => 0,
            "phpUnit" => "uuid: " . self::$uuid
        );

        self::$getUserTestData = array(
            'userID' => '2',
            'login_count' => '0',
            'statusID' => '1',
            'email' => 'phpunit@openwebpresence.com',
            'first_name' => 'phpUnit',
            'last_name' => 'Test',
            'uuid' => self::$uuid,
            'welcome_email_sent' => '0',
            'reset_pass_uuid' => null,
            'user_ip' => null,
            'full_name' => 'phpUnit Test',
            'is_admin' => null,
            'hide_ads' => null,
            'is_dev' => null,
            "phpUnit" => "uuid: " . self::$uuid,
            'rememberme_hash' => null
        );

        self::$owpUsers = new OwpUsers();
    }

    protected function setUp()
    {
        $_SESSION = owpUsers_test::$shared_session;
    }

    public function tearDown()
    {
        owpUsers_test::$shared_session = $_SESSION;
    }

    /**
     * @expectedException Exception
     * @covers OwpUsers::addUser
     */
    public function testAddUserFromInValidData()
    {
        $copyOfData = self::$createUserTestData;
        $copyOfData["email"] = "bademail";
        self::$owpUsers->addUser($copyOfData);
    }

    /**
     * @covers OwpUsers::addUser
     */
    public function testAddUserFromValidData()
    {
        self::$userID = self::$owpUsers->addUser(self::$createUserTestData);

        $this->assertEquals(
            2,
            self::$userID
        );
    }

    /**
     * @covers OwpUsers::validateUserPassword
     * @depends testAddUserFromValidData
     */
    public function testValidateUserPassword()
    {
        $passwordMatches = self::$owpUsers->validateUserPassword(self::$userID, self::$createUserTestData["passwd"]);

        $this->assertEquals(
            true,
            $passwordMatches
        );
    }

    /**
     * @depends testValidateUserPassword
     * @covers OwpUsers::get_user_record_byID
     * @covers OwpUsers::get_user_record_byUUID_noMeta
     */
    public function testCheckUserValidated()
    {
        $rowBefore = self::$owpUsers->get_user_record_byID(self::$userID);
        $recordExists = self::$owpUsers->get_user_record_byUUID_noMeta($rowBefore["uuid"]);

        $this->assertEquals(
            true,
            ($recordExists?true:false)
        );
    }

    /**
     * @depends testCheckUserValidated
     * @covers OwpUsers::updateUser
     * @covers OwpUsers::get_user_record_byID
     */
    public function testUpdateUserValidated()
    {
        $dataArray = array(
            "statusID"=>2,
            "uuid"=>null
        );

        self::$getUserTestData["statusID"] = 2;

        self::$owpUsers->updateUser(self::$userID, $dataArray);

        $rowAfter = self::$owpUsers->get_user_record_byID(self::$userID);

        $this->assertEquals(
            $dataArray, array(
                "statusID"=>2,
                "uuid"=>null
            )
        );
    }

    /**
     * @depends testUpdateUserValidated
     * @covers OwpUsers::userLoginViaUserID
     * @covers OwpUsers::userData
     */
    public function testUserLoginViaUserID()
    {
        self::$owpUsers->userLoginViaUserID(self::$userID);
        $userDataRow = self::$owpUsers->userData(self::$userID);

        file_put_contents("./logs/userDataRow.json", json_encode($userDataRow));

        if($userDataRow) {
            unset($userDataRow["passwd"]);
            unset($userDataRow["user_created_datetime"]);
            unset($userDataRow["user_updated_datetime"]);
            unset($userDataRow["user_last_login_datetime"]);
            self::$getUserTestData["uuid"] = $userDataRow["uuid"];
        }

        $this->assertEquals(self::$getUserTestData, $userDataRow);
    }

    /**
     * @depends testUserLoginViaUserID
     * @covers OwpUsers::logOut
     */
    public function testLogOut()
    {
        $this->assertEquals(
            false,
            self::$owpUsers->logOut()
        );
    }

    /**
     * @depends testAddUserFromValidData
     * @covers OwpUsers::userLogin
     */
    public function testUserLogin()
    {
        self::$owpUsers->userLogin(self::$createUserTestData["email"], self::$passwd);
        $this->assertEquals(self::$owpUsers->userID(), self::$userID);
    }

    /**
     * @depends testUserLogin
     * @covers OwpUsers::isLoggedIn
     */
    public function testIsLoggedIn()
    {
        $this->assertTrue(self::$owpUsers->isLoggedIn());
    }

    /**
     * @depends testIsLoggedIn
     * @covers OwpUsers::updatePassword
     * @covers OwpUsers::logOut
     * @covers OwpUsers::userLogin
     */
    public function testUpdatePassword()
    {
        self::$owpUsers->isLoggedIn();
        self::$owpUsers->updatePassword(self::$passwdSecond, self::$userID);
        self::$owpUsers->logOut();
        self::$owpUsers->userLogin(self::$createUserTestData["email"], self::$passwdSecond);

        $this->assertEquals(self::$owpUsers->userID(), self::$userID);
    }

    /**
     * @depends testUserLogin
     * @covers OwpUsers::logOut
     * @covers OwpUsers::setLostPassUUID
     */
    public function testSetLostPassUUID()
    {
        self::$owpUsers->logOut();
        $rowBefore = self::$owpUsers->get_user_record_byID_noMeta(self::$userID);
        $rowAfter = self::$owpUsers->setLostPassUUID(self::$userID);
        self::$lostPassUUID = $rowAfter["reset_pass_uuid"];

        $this->assertNotEquals($rowBefore["reset_pass_uuid"], $rowAfter["reset_pass_uuid"]);
    }

    /**
     * @depends testSetLostPassUUID
     * @covers OwpUsers::getUserIDViaLostPassUUID
     */
    public function testGetUserIDViaLostPassUUID()
    {
        $lpUserID = (int)self::$owpUsers->getUserIDViaLostPassUUID(self::$lostPassUUID);
        $this->assertEquals($lpUserID, self::$userID);
    }

    /**
     * @depends testGetUserIDViaLostPassUUID
     * @covers OwpUsers::clearLostPassUUID
     * @covers OwpUsers::userLoginViaUserID
     */
    public function testClearLostPassUUID()
    {
        $activeStatusID = 2;
        self::$owpUsers->clearLostPassUUID(self::$userID, $activeStatusID);
        self::$owpUsers->userLoginViaUserID(self::$userID);
        $rowAfter = self::$owpUsers->get_user_record_byID_noMeta(self::$userID);

        $this->assertEquals($rowAfter["statusID"], $activeStatusID);
    }

    /**
     * @depends testAddUserFromValidData
     * @covers OwpUsers::updateUser
     */
    public function testUpdateUser()
    {
        $dataArray = array(
            "NewUpdateMetaColumn"=>"123",
            "phpUnit"=>"testUpdateUser"
        );
        self::$owpUsers->updateUser(self::$userID, $dataArray);

        $rowAfter = self::$owpUsers->get_user_record_byID(self::$userID);

        $this->assertEquals(
            $dataArray, array(
                "NewUpdateMetaColumn"=>$rowAfter["NewUpdateMetaColumn"],
                "phpUnit"=>$rowAfter["phpUnit"]
            )
        );
    }

    /**
     * @depends testAddUserFromValidData
     * @covers OwpUsers::userExistsViaEmail
     */
    public function testUserExistsViaEmail()
    {
        $this->assertTrue(self::$owpUsers->userExistsViaEmail(self::$createUserTestData["email"]));
    }

    /**
     * @depends testAddUserFromValidData
     * @covers OwpUsers::updateLoginCount
     */
    public function testUpdateLoginCount()
    {
        $rowBefore = self::$owpUsers->get_user_record_byID_noMeta(self::$userID);
        self::$owpUsers->updateLoginCount(self::$userID);
        $rowAfter = self::$owpUsers->get_user_record_byID_noMeta(self::$userID);
        $this->assertLessThan($rowAfter["login_count"], $rowBefore["login_count"]);
    }

    /**
     * @depends testAddUserFromValidData
     * @covers OwpUsers::deleteUser
     */
    public function testDeleteUser()
    {
        $this->assertTrue(self::$owpUsers->deleteUser(self::$userID));
    }
}
