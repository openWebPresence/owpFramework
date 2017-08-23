<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpUsers
 * @author    Brian Tafoya <btafoya@briantafoya.com>
 * @version   1.0
 * @license   MIT
 * @license   https://opensource.org/licenses/MIT The MIT License
 * @category  OpenWebPresence_Support_Library
 * @link      http://openwebpresence.com OpenWebPresence
 * @uses      https://github.com/ezSQL/ezSQL EzSQL Database Abstraction
 * @uses      https://github.com/hautelook/phpass Openwall Phpass, modernized
 * @uses      https://github.com/egulias/EmailValidator EmailValidator
 *
 * Copyright (c) 2017, Brian Tafoya
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */


use Hautelook\Phpass\PasswordHash;
use Egulias\EmailValidator\EmailValidator;
use Egulias\EmailValidator\Validation\DNSCheckValidation;
use Egulias\EmailValidator\Validation\MultipleValidationWithAnd;
use Egulias\EmailValidator\Validation\RFCValidation;
use Egulias\EmailValidator\Validation\SpoofCheckValidation;

/**
 * Class OwpUserException
 */
class OwpUserException extends OwpException
{
}//end class


/**
 * OpenWebPresence User Support Library
 */
class OwpUsers
{

    /**
     * @var array $errors Error array
     */
    public $errors = false;

    /**
     * @var int $userID OpenWebPresence support methods
     */
    public $userID = 0;

    /**
     * @var object $ezSqlDB ezSQL Database Object
     */
    protected $ezSqlDB;

    /**
     * @var string $current_web_root The web root url
     */
    protected $current_web_root;

    /**
     * @var string $root_path Set the root file path.
     */
    public $root_path;

    /**
     * @var string $uuid Set the root file path.
     */
    public $uuid;

    /**
     * @var string $requested_action Requested action.
     */
    public $requested_action;

    /**
     * @var object $owp_SupportMethods OpenWebPresence support methods
     */
    protected $owp_SupportMethods;

    /**
     * @var object $SqueakyMindsPhpHelper SqueakyMindsPhpHelper support methods
     */
    protected $SqueakyMindsPhpHelper;

    /**
     * @var object $PhpConsole PhpConsole object.
     */
    public $PhpConsole;

    /**
     * @var object $OwpDBMySQLi DB object.
     */
    public $OwpDBMySQLi;


    /**
     * Constructor
     *
     * @method void __construct()
     * @access public
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __construct()
    {
        $this->current_web_root = CURRENT_WEB_ROOT;
        $this->root_path = ROOT_PATH;
        $this->OwpDBMySQLi = new OwpDBMySQLi($_ENV["DB_USER"], $_ENV["DB_PASS"], $_ENV["DB_NAME"], $_ENV["DB_HOST"]);
    }//end __construct()


    /**
     * Debug
     *
     * @method void __debugInfo()
     * @access public
     * @uses   $this->isAdmin()
     * @uses   $this->userData()
     * @uses   self::userID()
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo()
    {
        return [
            "isAdmin" => $this->isAdmin(),
            "MySQL_Errors" => $this->OwpDBMySQLi->captured_errors,
            "userData" => $this->userData(),
            "userID" => self::userID(),
        ];

    }//end __debugInfo()


    /**
     * addUser
     *
     * @method integer addUser($data_array) Create a user record
     * @access public
     *
     * @param array $data_array User array used to create the user's record. ("email", "passwd", "first_name", "last_name", "statusID", "welcome_email_sent", "uuid")
     *
     * @return integer userID
     *
     * @throws InvalidArgumentException Thrown when the provided argument is not valid user data, code 20
     * @throws Exception Thrown on standard failures, code 10, UDF failures code 30
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function addUser($data_array)
    {

        $required_columns = array(
            "email",
            "passwd",
            "first_name",
            "last_name",
            "statusID",
            "welcome_email_sent",
        );

        $missing_columns = array_diff($required_columns, array_keys($data_array));

        if ($missing_columns) {
            throw new Exception("The following columns are missing: " . implode(", ", $missing_columns));
        }

        // Execute owpUDF_On_addUserValiateData user defined function
        if (function_exists("owpUDF_On_addUserValiateData")) {
            $owpUDF_On_addUserValiateData = owpUDF_On_addUserValiateData(array("db" => $this->OwpDBMySQLi, "data_array" => $data_array));
            if ($owpUDF_On_addUserValiateData) {
                throw new Exception($owpUDF_On_addUserValiateData, 30);
            }
        }

        $validator = new EmailValidator();
        $multipleValidations = new MultipleValidationWithAnd(
            [
                new RFCValidation(),
                new DNSCheckValidation(),
                new SpoofCheckValidation()
            ]
        );

        if (!$validator->isValid($data_array["email"], $multipleValidations)) {
            throw new Exception("Invalid Email: " . (string)$data_array["email"]);
        }

        if (!self::validateStatusID((int)$data_array["statusID"])) {
            throw new Exception("Invalid statusID: " . (int)$data_array["statusID"]);
        }

        if (isset($data_array["user_created_datetime"])) {
            unset($data_array["user_created_datetime"]);
        }

        if (isset($data_array["user_updated_datetime"])) {
            unset($data_array["user_updated_datetime"]);
        }

        if (isset($data_array["user_last_login_datetime"])) {
            unset($data_array["user_last_login_datetime"]);
        }

        if (isset($data_array["reset_pass_uuid"])) {
            unset($data_array["reset_pass_uuid"]);
        }

        if (isset($data_array["user_ip"])) {
            unset($data_array["user_ip"]);
        }

        if (isset($data_array["userID"])) {
            unset($data_array["userID"]);
        }

        $new_hash = $this->genPasswdHash($data_array["passwd"]);

        $query_sql = "
            INSERT INTO tbl_users
            SET tbl_users.email = LCASE('" . filter_var($data_array["email"], FILTER_SANITIZE_EMAIL) . "'),
                tbl_users.passwd = '" . (string)$new_hash . "',
                tbl_users.first_name = '" . $this->OwpDBMySQLi->escape($data_array["first_name"]) . "',
                tbl_users.last_name = '" . $this->OwpDBMySQLi->escape($data_array["last_name"]) . "',
                tbl_users.statusID = " . (int)$data_array["statusID"] . ",
                tbl_users.user_created_datetime = SYSDATE(),
                tbl_users.user_updated_datetime = SYSDATE(),
                tbl_users.user_last_login_datetime = SYSDATE(),
                tbl_users.login_count = 0,
                tbl_users.welcome_email_sent = " . (int)$data_array["welcome_email_sent"] . ",
                tbl_users.uuid = '" . OwpSupportMethods::uuid() . "',
                tbl_users.reset_pass_uuid = NULL,
                tbl_users.user_ip = NULL";

        $this->userID = ($this->OwpDBMySQLi->query($query_sql) ? (int)$this->OwpDBMySQLi->insert_id : false);

        if (!$this->userID) {
            throw new Exception("Insert tbl_users failed: " . $this->OwpDBMySQLi->last_error, 10);
        }

        $query_sql = "
            SELECT * FROM tbl_users WHERE tbl_users.userID = " . (int)$this->userID . " LIMIT 1
        ";

        $current_user_row = $this->OwpDBMySQLi->get_row($query_sql, ARRAY_A);

        if (!$current_user_row) {
            throw new Exception("Get user failed: " . $this->OwpDBMySQLi->MySQLFirephpGetLastMysqlError(), 10);
        }

        $core_keys = $current_user_row;
        unset($core_keys["userID"]);
        $meta_keys = array_diff_key($data_array, $current_user_row);

        if ($meta_keys && is_array($meta_keys)) {
            foreach ($meta_keys as $dk => $dv) {
                $query_sql = "
                    REPLACE INTO tbl_users_meta_data
                    SET
                        tbl_users_meta_data.key_name = '" . (string)$dk . "',
                        tbl_users_meta_data.key_value = '" . (string)$this->OwpDBMySQLi->escape($dv) . "',
                        tbl_users_meta_data.userID = " . (int)$this->userID . ",
                        tbl_users_meta_data.updated_ts = SYSDATE()";

                $this->OwpDBMySQLi->query($query_sql);
            }
        }

        // Execute owpUDF_On_addUserSuccess user defined function
        if (function_exists("owpUDF_On_addUserSuccess")) {
            $owpUDF_On_addUserSuccess = owpUDF_On_addUserSuccess(array("userID" => (int)$this->userID, "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_addUserSuccess) {
                throw new Exception((string)$owpUDF_On_addUserSuccess, 30);
            }
        }

        return (int)$this->userID;

    }//end addUser()


    /**
     * clearLostPassUUID
     *
     * @method boolean clearLostPassUUID($userID, $statusID) Reset the lost pass UUID and set the statusID, for example when a lost password request has been completed
     * @access public
     *
     * @param int $userID   Existing userID
     * @param int $statusID New user statusID
     *
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function clearLostPassUUID($userID, $statusID)
    {
        return $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.statusID = " . (int)$statusID . ",
                tbl_users.reset_pass_uuid = NULL
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

    }//end clearLostPassUUID()


    /**
     * deleteUser
     *
     * @method boolean deleteUser($userID) Permanently remove a user record.
     * @access public
     *
     * @param int $userID Existing userID
     *
     * @return boolean Success or failure
     *
     * @throws Exception Thrown on UDF failures code 30
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function deleteUser($userID)
    {

        if (!(int)$userID) {
            return false;
        }

        $query_sql = "
            DELETE FROM tbl_users
            WHERE tbl_users.userID = " . (int)$userID . " LIMIT 1";

        $this->OwpDBMySQLi->query($query_sql);

        $query_sql = "
            DELETE FROM tbl_users_meta_data
            WHERE tbl_users_meta_data.userID = " . (int)$userID;

        $this->OwpDBMySQLi->query($query_sql);

        $query_sql = "
            DELETE FROM tbl_users_rights
            WHERE tbl_users_rights.userID = " . (int)$userID;

        $this->OwpDBMySQLi->query($query_sql);

        // Execute owpUDF_On_deleteUser user defined function
        if (function_exists("owpUDF_On_deleteUser")) {
            $owpUDF_On_deleteUser = owpUDF_On_deleteUser(array("userID" => (int)$userID, "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_deleteUser) {
                throw new Exception((string)$owpUDF_On_deleteUser, 30);
            }
        }

        // unset the session if the user being deleted is logged in.
        if ((int)self::userID() === (int)$userID && isset($_SESSION["userData"])) {
            unset($_SESSION["userData"]);
        }

        return true;

    }//end deleteUser()


    /**
     * genPasswdHash
     *
     * @method string genPasswdHash($password) Generate a password hash
     * @param  string $password User's password
     *
     * @return string Hashed password
     * @access public
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function genPasswdHash($password)
    {
        $PasswordHash = new PasswordHash(8, false);

        return $PasswordHash->HashPassword($password);

    }//end genPasswdHash()


    /**
     * get_user_info_row
     *
     * @method mixed get_user_info_row($where_clause) Retrieve a user's data based on a dynamic WHERE statement
     * @access private
     *
     * @param string $where_clause The string WHERE statement used in tbl_users select
     *
     * @return mixed User array or false on failure
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function get_user_info_row($where_clause)
    {
        $get_user_record_noMeta = $this->get_user_record_noMeta($where_clause);

        if ($get_user_record_noMeta) {
            return array_merge((array)$get_user_record_noMeta, (array)$this->getUserMetaData((int)$get_user_record_noMeta["userID"]));
        } else {
            return false;
        }

    }//end get_user_info_row()


    /**
     * getUserMetaData
     *
     * @method getUserMetaData($userID) Get only the user meta data
     * @access public
     *
     * @param int $userID Existing userID
     *
     * @return array
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function getUserMetaData($userID)
    {
        $response = array();

        $query_sql = "
            SELECT
                tbl_users_meta_data.key_name,
                tbl_users_meta_data.key_value
            FROM
                tbl_users_meta_data
            WHERE
                tbl_users_meta_data.userID = " . (int)$userID . "
            ORDER BY tbl_users_meta_data.key_name";

        $userData = $this->OwpDBMySQLi->get_results($query_sql);

        if ($userData) {
            foreach ($userData as $ud) {
                $response[$ud->key_name] = $ud->key_value;
            }
        }

        return $response;

    }//end getUserMetaData()


    /**
     * get_user_record_byEMAIL
     *
     * @method mixed get_user_record_byEMAIL($email) Retrieve a user's data (with meta) based on email, without setting a session
     * @access public
     *
     * @param string $email The string Existing Email
     *
     * @return mixed User array or false on failure
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function get_user_record_byEMAIL($email)
    {
        $get_user_record_noMeta = $this->get_user_record_noMeta(" WHERE tbl_users.email = '" . $email . "'");

        if ($get_user_record_noMeta) {
            return array_merge((array)$get_user_record_noMeta, (array)$this->getUserMetaData((int)$get_user_record_noMeta["userID"]));
        } else {
            return false;
        }

    }//end get_user_record_byEMAIL()


    /**
     * get_user_record_byID
     *
     * @method mixed get_user_record_byID($userID) Retrieve a user's data (with meta) based on userID, without setting a session
     * @access public
     *
     * @param int $userID The string Existing userID
     *
     * @return mixed User array or false on failure
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function get_user_record_byID($userID)
    {
        $get_user_record_noMeta = $this->get_user_record_noMeta(" WHERE tbl_users.userID = " . $userID);

        if ($get_user_record_noMeta) {
            return array_merge((array)$get_user_record_noMeta, (array)$this->getUserMetaData((int)$get_user_record_noMeta["userID"]));
        } else {
            return false;
        }

    }//end get_user_record_byID()


    /**
     * get_user_record_byID_noMeta
     *
     * @method array get_user_record_byID_noMeta($userID) Retrieve base user data without any meta data by userID
     * @access public
     *
     * @param int $userID Existing userID
     *
     * @return array User array, false of failure
     * @uses   OwpUsers::get_user_record_noMeta()
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function get_user_record_byID_noMeta($userID)
    {
        return $this->get_user_record_noMeta(" WHERE tbl_users.userID = " . $userID);

    }//end get_user_record_byID_noMeta()


    /**
     * get_user_record_byUUID_noMeta
     *
     * @method array get_user_record_byUUID_noMeta($uuid) Retrieve base user data without any meta data by uuid
     * @access public
     *
     * @param string $uuid Existing UUID
     *
     * @return array User array, false of failure
     * @uses   OwpUsers::get_user_record_noMeta()
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function get_user_record_byUUID_noMeta($uuid)
    {
        return $this->get_user_record_noMeta(" WHERE tbl_users.uuid = '" . $uuid . "'");

    }//end get_user_record_byUUID_noMeta()


    /**
     * get_user_record_noMeta
     *
     * @method array get_user_record_noMeta($where_clause) Retrieve base user data without any meta data by WHERE statement
     * @access public
     *
     * @param string $where_clause The string WHERE statement used in tbl_users select
     *
     * @return array User array, false of failure
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function get_user_record_noMeta($where_clause)
    {

        $query_sql = "
            SELECT tbl_users.*,
                CONCAT_WS(' ',TRIM(tbl_users.first_name),TRIM(tbl_users.last_name)) AS `full_name`,
                tbl_users_rights.is_admin,
                tbl_users_rights.hide_ads,
                tbl_users_rights.is_dev
            FROM tbl_users
            LEFT JOIN tbl_users_rights ON tbl_users_rights.userID = tbl_users.userID ";
        $query_sql .= "	" . $where_clause . " ";
        $query_sql .= "	LIMIT 1";

        return $this->OwpDBMySQLi->get_row($query_sql, ARRAY_A);
    }//end get_user_record_noMeta()


    /**
     * getUserIDViaLostPassUUID
     *
     * @method int getUserIDViaLostPassUUID($reset_pass_uuid) Get the userID via the lost UUID token, previously set by setLostPassUUID()
     * @access public
     * @see    OwpUsers::setLostPassUUID
     *
     * @param string $reset_pass_uuid UUID token usually sent via email for validation
     *
     * @return integer
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function getUserIDViaLostPassUUID($reset_pass_uuid)
    {
        return (int)$this->OwpDBMySQLi->get_var(
            "
            SELECT tbl_users.userID
            FROM tbl_users
            WHERE tbl_users.reset_pass_uuid = '" . (string)$reset_pass_uuid . "'
            LIMIT 1"
        );

    }//end getUserIDViaLostPassUUID()


    /**
     * getUserRecord
     *
     * @method int getUserRecord($userID) Get user record.
     * @access public
     *
     * @param integer $userID UserID
     *
     * @return mixed
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function getUserRecord($userID)
    {
        $OwpUsers = new OwpUsers();
        $a1 = $OwpUsers->get_user_record_byID_noMeta($userID);
        if (!$a1) {
            return false;
        }
        unset($a1["passwd"]);
        $a2 = $OwpUsers->getUserMetaData((int)$userID);

        return OwpSupportMethods::OwpPCdebug((array)array_merge($a1, $a2), "OwpUsers.getUserRecord");

    }//end getUserRecord()


    /**
     * isAdmin
     *
     * @method isAdmin() Returns true if the user is flagged as an admin
     * @access public
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function isAdmin()
    {
        if (isset($_SESSION["userData"]) && (int)$_SESSION["userData"]["is_admin"]) {
            return true;
        } else {
            return false;
        }

    }//end isAdmin()


    /**
     * isLoggedIn
     *
     * @method isLoggedIn() Returns true if a valid user session exists
     * @access public
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function isLoggedIn()
    {
        if (isset($_SESSION["userData"])) {
            return true;
        } else {
            return false;
        }

    }//end isLoggedIn()


    /**
     * logOut
     *
     * @method logOut() Log the user out, clearing the user session
     * @access public
     * @return boolean
     *
     * @throws Exception Thrown on UDF failures code 30
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function logOut()
    {
        // Execute owpUDF_On_logOut user defined function
        if (function_exists("owpUDF_On_logOut")) {
            $owpUDF_On_logOut = owpUDF_On_logOut(array("userID" => (int)self::userID(), "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_logOut) {
                throw new Exception($owpUDF_On_logOut, 30);
            }
        }

        if (isset($_SESSION["userData"])) {
            unset($_SESSION["userData"]);
        }

        if (isset($_COOKIE["owpSite"])) {
            $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
            setcookie("owpSite", "", 0, "/", $domain, false);
            unset($_COOKIE["owpSite"]);
        }

        return (boolean)isset($_SESSION["userData"]);

    }//end logOut()


    /**
     * refresh_user_session
     *
     * @method refresh_user_session() Refresh the session data from the user's record
     * @access public
     * @return boolean
     * @throws Exception On refresh
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function refresh_user_session()
    {
        if ((int)self::userID()) {
            $a1 = $this->get_user_record_noMeta(" WHERE tbl_users.userID = " . (int)self::userID());
            if (!$a1) {
                throw new Exception("OwpUsers.refresh_user_session.this.get_user_record_noMeta Failed!");
            }

            $a2 = $this->getUserMetaData((int)self::userID());
            if ($a2) {
                $_SESSION["userData"] = array_merge($a1, $a2);
            } else {
                $_SESSION["userData"] = $a1;
            }

            return true;
        }

        return false;

    }//end refresh_user_session()


    /**
     * rememberMe
     *
     * @method rememberMe() Load the user's profile based on the remember me cookie if the hash matches
     * @access public
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function rememberMe()
    {

        if (!(int)self::userID()) {

            if (!SqueakyMindsPhpHelper::cookievar("owpSite")) {
                return false;
            }

            $owpSite = json_decode(SqueakyMindsPhpHelper::cookievar("owpSite"), true);

            $a1 = $this->get_user_record_noMeta(" WHERE tbl_users.userID = " . (int)$owpSite["id"]);

            if (!$a1) {
                return false;
            }

            $PasswordHash = new PasswordHash(8, false);

            $matches = $PasswordHash->CheckPassword((string)$owpSite["rememberme"], $a1["rememberme_hash"]);

            if (!$matches) {
                return false;
            }

            $this->userLoginViaUserID((int)$owpSite["id"]);
            $_SESSION["rememberMe"] = true;
            return true;
        }

        return false;

    }//end rememberMe()


    /**
     * rememberMeSet
     *
     * @method rememberMeSet() Set the remember me cookie
     * @access public
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function rememberMeSet()
    {
        $uuid = SqueakyMindsPhpHelper::uuid();
        $new_hash = $this->genPasswdHash($uuid);

        $value = json_encode(array("id" => self::userID(), "rememberme" => $uuid));
        $domain = ($_SERVER['HTTP_HOST'] != 'localhost') ? $_SERVER['HTTP_HOST'] : false;
        setcookie("owpSite", $value, strtotime('+30 days'), "/", $domain, false);

        $this->OwpDBMySQLi->query(
            "
                UPDATE tbl_users
                SET tbl_users.rememberme_hash = '" . (string)$new_hash . "'
                WHERE tbl_users.userID = " . (int)self::userID() . "
                LIMIT 1"
        );

        return $this->rememberMe();
    }//end rememberMeSet()


    /**
     * sanityCheck
     *
     * @method sanityCheck() Validate that the user session still exists, if not redirect them
     * @access public
     *
     * @param string $redirect_url (optional) URL to redirect to should the user sanity check show the user is not logged in
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function sanityCheck($redirect_url = "")
    {
        if ((int)self::userID()) {
            $query_sql = "
                SELECT COUNT(*)
                FROM tbl_users
                WHERE tbl_users.userID = " . (int)self::userID() . "
                LIMIT 1";

            $result = $this->OwpDBMySQLi->get_var($query_sql);

            if ((int)$result === 0) {
                $this->logOut();
                ob_clean();
                if (strlen($redirect_url)) {
                    header('Location: ' . $redirect_url);
                } else {
                    header('Location: ' . "http" . ($_SERVER['HTTPS'] ? "s" : "") . "://" . $_SERVER["SERVER_NAME"] . "/");
                }

                exit();
            }
        }//end if

    }//end sanityCheck()


    /**
     * saveUserMetaDataItem
     *
     * @method saveUserMetaDataItem($userID, $key_name, $key_value) Set user meta data
     * @access public
     * @param  $userID
     * @param  $key_name
     * @param  $key_value
     * @return bool
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function saveUserMetaDataItem($userID, $key_name, $key_value)
    {
        $query_sql = "
            REPLACE INTO tbl_users_meta_data
            SET
                tbl_users_meta_data.key_name = '" . $this->OwpDBMySQLi->escape((string)$key_name) . "',
                tbl_users_meta_data.userID = " . (int)$userID . ",
                tbl_users_meta_data.key_value = '" . $this->OwpDBMySQLi->escape((string)$key_value) . "',
                tbl_users_meta_data.updated_ts = SYSDATE()
        ";

        $userData = (boolean)$this->OwpDBMySQLi->query($query_sql);

        return $userData;

    }//end saveUserMetaDataItem()


    /**
     * setLostPassUUID
     *
     * @method setLostPassUUID($userID) Create a lost password recovery UUID token
     * @access public
     * @see    OwpUsers::getUserIDViaLostPassUUID()
     * @see    OwpUsers::updatePassword()
     *
     * @param int $userID Existing userID
     *
     * @return array
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function setLostPassUUID($userID)
    {
        $reset_pass_uuid = OwpSupportMethods::uuid();

        $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.reset_pass_uuid = '" . (string)$reset_pass_uuid . "',
                tbl_users.statusID = 4
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

        return $this->get_user_info_row("WHERE tbl_users.userID = " . (int)$userID);

    }//end setLostPassUUID()


    /**
     * setLostPassUUIDViaEmail
     *
     * @method setLostPassUUIDViaEmail($email) Create a lost password recovery UUID token
     * @access public
     * @see    OwpUsers::getUserIDViaLostPassUUID()
     * @see    OwpUsers::updatePassword()
     *
     * @param string $email Existing user email
     *
     * @return string
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function setLostPassUUIDViaEmail($email)
    {
        $reset_pass_uuid = OwpSupportMethods::uuid();

        $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.reset_pass_uuid = '" . (string)$reset_pass_uuid . "',
                tbl_users.statusID = 4
            WHERE tbl_users.email = '" . (string)$email . "'
            LIMIT 1"
        );

        return $this->get_user_info_row("WHERE LCASE(tbl_users.email) = LCASE('" . filter_var($email, FILTER_SANITIZE_EMAIL) . "')");

    }//end setLostPassUUIDViaEmail()


    /**
     * setStatusID
     *
     * @method setStatusID($userID, $statusID) Set the user status ID
     * @access public
     *
     * @param int $userID   Existing userID
     * @param int $statusID Existing valid statusID
     * @see   OwpUsers::validateStatusID()
     *
     * @throws Exception Thrown on UDF failures code 30
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function setStatusID($userID, $statusID)
    {
        $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.statusID = " . (int)$statusID . "
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

        // Execute owpUDF_On_setStatusID user defined function
        if (function_exists("owpUDF_On_setStatusID")) {
            $owpUDF_On_setStatusID = owpUDF_On_setStatusID(array("userID" => (int)self::userID(), "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_setStatusID) {
                throw new Exception($owpUDF_On_setStatusID, 30);
            }
        }

    }//end setStatusID()


    /**
     * setStatusIdWhereInList
     *
     * @method setStatusIdWhereInList($userID, $statusID, $statusIDList) Set the user status ID
     * @access public
     *
     * @param int    $userID       Existing userID
     * @param int    $statusID     Existing valid statusID
     * @param string $statusIDList
     * @see   OwpUsers::validateStatusID()
     *
     * @throws Exception Thrown on UDF failures code 30
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function setStatusIdWhereInList($userID, $statusID, $statusIDList)
    {
        $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.statusID = " . (int)$statusID . "
            WHERE tbl_users.userID = " . (int)$userID . "
            AND tbl_users.statusID IN (" . (string)$statusIDList . ")
            LIMIT 1"
        );

        // Execute owpUDF_On_setStatusID user defined function
        if (function_exists("owpUDF_On_setStatusID")) {
            $owpUDF_On_setStatusID = owpUDF_On_setStatusID(array("userID" => (int)self::userID(), "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_setStatusID) {
                throw new Exception($owpUDF_On_setStatusID, 30);
            }
        }

    }//end setStatusIdWhereInList()


    /**
     * userData
     *
     * @method userData() Get user data for the logged in user
     * @access public
     * @return mixed User data array or false if it fails
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function userData()
    {
        if (self::isLoggedIn()) {
            return $_SESSION["userData"];
        } else {
            return false;
        }

    }//end userData()


    /**
     * userDataItem
     *
     * @method userDataItem() Get a user data item for the logged in user
     * @access public
     * @param  string $item           User data item column name
     * @param  string $alternate_data String info as an alternative to the userDataItem when it does not exist
     * @param  string $append_to_item String info to append to the userDataItem
     * @return mixed userData
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function userDataItem($item, $alternate_data = "", $append_to_item = "")
    {
        if (self::userData() && isset($_SESSION["userData"][(string)$item])) {
            return (string)$_SESSION["userData"][(string)$item] . (strlen((string)$append_to_item) ? " " . $append_to_item : "");
        } else {
            return (string)$alternate_data . (strlen((string)$append_to_item) ? " " . $append_to_item : "");
        }

    }//end userDataItem()


    /**
     * userID
     *
     * @method userID() Return the current userID
     * @access public
     * @return integer userID
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    static public function userID()
    {
        return (int)self::userDataItem("userID", 0);

    }//end userID()


    /**
     * updateLoginCount
     *
     * @method updateLoginCount($userID) Updating the login counter as well as the user's current IP address
     * @access public
     *
     * @param int $userID Existing userID
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function updateLoginCount($userID)
    {
        $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.login_count = tbl_users.login_count + 1,
                tbl_users.user_last_login_datetime = SYSDATE(),
                tbl_users.user_ip = '" . OwpSupportMethods::GetUserIP() . "'
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

    }//end updateLoginCount()


    /**
     * updateLostPassword
     *
     * @method updateLostPassword($password, $uuid) Usually used in conjunction with getUserIDViaLostPassUUID() and setLostPassUUID() for lost passwords or to the user to updated their own passwrd
     * @access public
     * @see    OwpUsers::getUserIDViaLostPassUUID()
     * @see    OwpUsers::setLostPassUUID()
     *
     * @param string $password New user password
     * @param string $uuid     Lost Pass UUID
     *
     * @throws Exception Thrown on UDF failures code 30
     *
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function updateLostPassword($password, $uuid)
    {
        $new_hash = $this->genPasswdHash($password);

        $this->OwpDBMySQLi->query('BEGIN');

        $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.passwd = '" . $new_hash . "',
                tbl_users.statusID = 2,
                tbl_users.reset_pass_uuid = NULL
            WHERE tbl_users.reset_pass_uuid = '" . (string)$uuid . "'
            LIMIT 1"
        );

        // Execute owpUDF_On_updatePassword user defined function
        if (function_exists("owpUDF_On_updatePassword")) {
            $owpUDF_On_updatePassword = owpUDF_On_updatePassword(array("userID" => (int)self::userID(), "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_updatePassword) {
                throw new Exception($owpUDF_On_updatePassword, 30);
            }
        }

        if ($this->OwpDBMySQLi->query('COMMIT') !== false) {
            return true;
        } else {
            // transaction failed, rollback
            $this->OwpDBMySQLi->query('ROLLBACK');
            $this->errors[] = array(
                "message" => "updateLostPassword: transaction failed, rollback.",
                "details" => array("last_mysql_error" => $this->OwpDBMySQLi->MySQLFirephpGetLastMysqlError()),
            );

            return false;
        }

    }//end updateLostPassword()


    /**
     * updatePassword
     *
     * @method updatePassword($password, $userID) Usually used in conjunction with getUserIDViaLostPassUUID() and setLostPassUUID() for lost passwords or to the user to updated their own passwrd
     * @access public
     * @see    OwpUsers::getUserIDViaLostPassUUID()
     * @see    OwpUsers::setLostPassUUID()
     *
     * @param string $password New user password
     * @param int    $userID   Existing userID
     *
     * @throws Exception Thrown on UDF failures code 30
     *
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function updatePassword($password, $userID)
    {
        $new_hash = $this->genPasswdHash($password);

        $this->OwpDBMySQLi->query('BEGIN');

        $this->OwpDBMySQLi->query(
            "
            UPDATE tbl_users
            SET tbl_users.passwd = '" . $new_hash . "'
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

        // Execute owpUDF_On_updatePassword user defined function
        if (function_exists("owpUDF_On_updatePassword")) {
            $owpUDF_On_updatePassword = owpUDF_On_updatePassword(array("userID" => (int)self::userID(), "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_updatePassword) {
                throw new Exception($owpUDF_On_updatePassword, 30);
            }
        }

        if ($this->OwpDBMySQLi->query('COMMIT') !== false) {
            return true;
        } else {
            // transaction failed, rollback
            $this->OwpDBMySQLi->query('ROLLBACK');
            $this->errors[] = array(
                "message" => "updatePassword: transaction failed, rollback.",
                "details" => array("last_mysql_error" => $this->OwpDBMySQLi->MySQLFirephpGetLastMysqlError()),
            );

            return false;
        }

    }//end updatePassword()


    /**
     * updateUser
     *
     * @method updateUser($userID, $data_array) Update an existing user record
     * @access public
     * @param  int   $userID     Existing userID
     * @param  array $data_array New user data
     * @return boolean
     * @throws InvalidArgumentException Provided argument is now valid userID
     * @throws Exception
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function updateUser($userID, $data_array)
    {

        if (!(int)$userID) {
            $this->errors[] = array(
                "message" => "updateUser: Invalid userID.",
                "details" => array("userID" => (int)$userID),
            );

            throw new InvalidArgumentException("Invalid userID " . (int)$userID, 10);
        }

        if (isset($data_array["passwd"])) {
            unset($data_array["passwd"]);
        }

        if (isset($data_array["user_created_datetime"])) {
            unset($data_array["user_created_datetime"]);
        }

        if (isset($data_array["user_updated_datetime"])) {
            unset($data_array["user_updated_datetime"]);
        }

        if (isset($data_array["user_ip"])) {
            unset($data_array["user_ip"]);
        }

        $query_sql = "
            SELECT * FROM tbl_users WHERE tbl_users.userID = " . (int)$userID . " LIMIT 1
        ";

        $current_user_row = $this->OwpDBMySQLi->get_row($query_sql, ARRAY_A);

        if (!$current_user_row) {
            $this->errors[] = array(
                "message" => "updateUser: current_user_row SELECT tbl_users failed.",
                "details" => array(
                    "query_sql" => $query_sql,
                    "last_mysql_error" => $this->OwpDBMySQLi->MySQLFirephpGetLastMysqlError(),
                ),
            );

            throw new InvalidArgumentException("Invalid userID " . (int)$userID, 10);
        }

        unset($current_user_row["user_created_datetime"]);
        unset($current_user_row["user_updated_datetime"]);
        unset($current_user_row["passwd"]);

        $core_keys = $current_user_row;
        unset($core_keys["userID"]);
        $meta_keys = array_diff_key($data_array, $current_user_row);

        $ck_sql_array = array();
        if ($core_keys) {
            foreach ($core_keys as $ckc => $ckv) {
                if (isset($data_array[$ckc]) && $data_array[$ckc] != $ckv) {
                    $ck_sql_array[] = " `tbl_users`.`" . (string)$ckc . "` = '" . $this->OwpDBMySQLi->escape((string)$data_array[$ckc]) . "' ";
                }
            }
        }

        $ck_sql_array[] = " `tbl_users`.`user_updated_datetime` = SYSDATE() ";

        $setStatement = implode(", ", $ck_sql_array);

        $query_sql = "
                UPDATE tbl_users
                SET $setStatement
                WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1";

        $this->OwpDBMySQLi->query($query_sql);

        $meta_key_exclude = array(
            "full_name",
            "hide_ads",
            "is_admin",
            "is_dev"
        );

        if ($meta_keys && is_array($meta_keys)) {
            foreach ($meta_keys as $dk => $dv) {
                if (!in_array($dk, $meta_key_exclude)) {
                    $query_sql = "
                    REPLACE INTO tbl_users_meta_data
                    SET
                        tbl_users_meta_data.key_name = '" . (string)$dk . "',
                        tbl_users_meta_data.key_value = '" . (string)$this->OwpDBMySQLi->escape($dv) . "',
                        tbl_users_meta_data.userID = " . (int)$userID . ",
                        tbl_users_meta_data.updated_ts = SYSDATE()";

                    $this->OwpDBMySQLi->query($query_sql);
                }
            }
        }

        // Execute owpUDF_On_updateUser user defined function
        if (function_exists("owpUDF_On_updateUser")) {
            $owpUDF_On_updateUser = owpUDF_On_updateUser(array("userID" => (int)$userID, "db" => $this->OwpDBMySQLi));
            if ($owpUDF_On_updateUser) {
                throw new Exception($owpUDF_On_updateUser, 30);
            }
        }

        return true;

    }//end updateUser()


    /**
     * updateUserAdminRights
     *
     * @method updateUserAdminRights($userID, $is_admin = 0, $hide_ads = 0, $is_dev = 0) Updating the login counter as well as the user's current IP address
     * @access public
     *
     * @param int $userID   New or Existing userID
     * @param int $is_admin (optional) Designate as an admin
     * @param int $hide_ads (optional) Hide ads
     * @param int $is_dev   (optional) Enable dev features
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function updateUserAdminRights($userID, $is_admin = 0, $hide_ads = 0, $is_dev = 0)
    {
        $this->OwpDBMySQLi->query(
            "
            REPLACE INTO tbl_users_rights
            SET tbl_users_rights.userID = " . (int)$userID . ",
                tbl_users_rights.is_admin = " . (int)$is_admin . ",
                tbl_users_rights.hide_ads = " . (int)$hide_ads . ",
                tbl_users_rights.is_dev = " . (int)$is_dev . "
            "
        );

    }//end updateUserAdminRights()


    /**
     * userExistsViaEmail
     *
     * @method userExistsViaEmail($email) Check if user exists via email
     * @access public
     * @param  string $email Valid user email address
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function userExistsViaEmail($email)
    {
        $query_sql = "
            SELECT COUNT(*)
            FROM tbl_users
            WHERE LCASE(tbl_users.email) = LCASE('" . filter_var($email, FILTER_SANITIZE_EMAIL) . "')
            LIMIT 1";

        return (boolean)((int)$this->OwpDBMySQLi->get_var($query_sql) ? true : false);

    }//end userExistsViaEmail()


    /**
     * userLogin
     *
     * @method userLogin($email, $passwd) User login via email and password
     * @access public
     * @param  string $email  Valid user email address
     * @param  string $passwd Valid user password
     * @return boolean
     * @throws OwpUserException User status information
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function userLogin($email, $passwd)
    {
        try {
            $exists = $this->userLoginCore("WHERE LCASE(tbl_users.email) = LCASE('" . filter_var($email, FILTER_SANITIZE_EMAIL) . "')");
        } catch (OwpUserException $e) {
            throw $e;
        }

        if ($exists) {
            $val_pass = $this->validate_password($passwd, $this->userDataItem("passwd"));

            if ($val_pass === true) {
                return self::userID();
            } else {
                if (isset($_SESSION["userData"])) {
                    unset($_SESSION["userData"]);
                }
            }
        }

        return false;

    }//end userLogin()


    /**
     * userLoginCore
     *
     * @method userLoginCore($where_statement) The core query method
     * @access private
     * @return boolean
     * @param  string $where_statement Where statement to be using within the user row query
     * @throws OwpUserException OwpUserException custom status handling
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function userLoginCore($where_statement)
    {
        if (isset($_SESSION["userData"])) {
            unset($_SESSION["userData"]);
        }

        $get_user_info_row = $this->get_user_info_row($where_statement);

        if ($get_user_info_row) {
            $statusInfo = (array)$this->OwpDBMySQLi->get_row(
                "
                SELECT * FROM tbl_users_status
                WHERE tbl_users_status.statusID = " . (int)$get_user_info_row["statusID"] . "
                LIMIT 1", ARRAY_A
            );

            if (!$statusInfo) {
                throw new OwpUserException("Invalid user status ID.", 911);
            }

            if ((int)$statusInfo["canLogin"] == 0) {
                throw new OwpUserException("Your account " . $statusInfo["status_label"], $statusInfo["statusID"]);
            }

            $_SESSION["userData"] = $get_user_info_row;

            $this->updateLoginCount((int)$get_user_info_row["userID"]);

            // Execute owpUDF_On_userLoginCore user defined function
            if (function_exists("owpUDF_On_userLoginCore")) {
                owpUDF_On_userLoginCore(array("userID" => (int)$get_user_info_row["userID"], "db" => $this->OwpDBMySQLi));
            }

            return true;
        } else {
            return false;
        }//end if

    }//end userLoginCore()


    /**
     * userLoginViaToken
     *
     * @method userLoginViaToken($uuid) User login via UUID token
     * @access public
     * @param  string $uuid User UUID token created with the record, not related to the lost password UUID
     * @return boolean
     * @throws OwpUserException User status information
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function userLoginViaToken($uuid)
    {
        try {
            return $this->userLoginCore("WHERE tbl_users.uuid = LCASE('" . $uuid . "')");
        } catch (OwpUserException $e) {
            throw $e;
        }

    }//end userLoginViaToken()


    /**
     * userLoginViaUserID
     *
     * @method userLoginViaUserID($userID) User login via UserID
     * @access public
     * @param  int $userID Existing userID
     * @return boolean
     * @throws OwpUserException User status information
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function userLoginViaUserID($userID)
    {
        try {
            return $this->userLoginCore("WHERE tbl_users.userID = " . (int)$userID);
        } catch (OwpUserException $e) {
            throw $e;
        }

    }//end userLoginViaUserID()


    /**
     * validate_password
     *
     * @method validate_password($passwd, $hash) Validate the passed password against the user hash supplied
     * @access private
     * @return mixed
     * @param  string $passwd Valid user password
     * @param  string $hash   Existing user hash for user record
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function validate_password($passwd, $hash)
    {
        $PasswordHash = new PasswordHash(8, false);

        return $PasswordHash->CheckPassword($passwd, $hash);

    }//end validate_password()


    /**
     * validateStatusID
     *
     * @method validateStatusID($statusID)
     * @access public
     * @return boolean
     * @param  int $statusID status ID to validate
     * @see    OwpUsers::setStatusID()
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function validateStatusID($statusID)
    {
        return (bool)$this->OwpDBMySQLi->get_var(
            "
            SELECT COUNT(*) FROM tbl_users_status
            WHERE tbl_users_status.statusID = " . (int)$statusID . "
            LIMIT 1"
        );

    }//end validateStatusID()


    /**
     * validateUserPassword
     *
     * @method userLogin($userID, $passwd) Validate user password via userID
     * @access public
     * @param  int    $userID Valid userID
     * @param  string $passwd Valid user password
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function validateUserPassword($userID, $passwd)
    {
        $get_user_info_row = $this->get_user_info_row("WHERE tbl_users.userID = " . (int)$userID);
        if ($get_user_info_row) {
            $val_pass = $this->validate_password($passwd, $get_user_info_row["passwd"]);
            if ($val_pass === true) {
                return true;
            }
        }

        return false;

    }//end validateUserPassword()


}//end class
