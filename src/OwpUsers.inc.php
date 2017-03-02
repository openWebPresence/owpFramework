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
     * Constructor
     *
     * @method void __construct()
     * @access public
     *
     * @param object $frameworkObject owp_SupportMethods Object
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __construct($frameworkObject)
    {
        $this->owp_SupportMethods = $frameworkObject["OwpSupportMethods"];
        $this->ezSqlDB = $frameworkObject["ezSqlDB"];
        $this->current_web_root = $frameworkObject["current_web_root"];
        $this->root_path = $frameworkObject["root_path"];
        $this->requested_action = $frameworkObject["requested_action"];
        $this->uuid = $frameworkObject["uuid"];
        $this->SqueakyMindsPhpHelper = $frameworkObject["SqueakyMindsPhpHelper"];
        $this->PhpConsole = $frameworkObject["PhpConsole"];
    }

    /**
     * Debug
     *
     * @method void __debugInfo()
     * @access public
     * @uses   $this->isAdmin()
     * @uses   $this->userData()
     * @uses   $this->userID()
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function __debugInfo()
    {
        return ["isAdmin" => $this->isAdmin(), "MySQL_Errors" => $this->ezSqlDB->captured_errors, "userData" => $this->userData(), "userID" => $this->userID(),];
    }

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

        $required_columns = array("email", "passwd", "first_name", "last_name", "statusID", "welcome_email_sent");

        $missing_columns = array_diff($required_columns, array_keys($data_array));

        if ($missing_columns) {
            throw new Exception("The following columns are missing: " . implode(", ", $missing_columns));
        }

        // Execute owpUDF_On_addUserValiateData user defined function
        if (function_exists("owpUDF_On_addUserValiateData")) {
            $owpUDF_On_addUserValiateData = owpUDF_On_addUserValiateData(array("db" => $this->ezSqlDB, "data_array" => $data_array));
            if ($owpUDF_On_addUserValiateData) {
                throw new Exception($owpUDF_On_addUserValiateData, 30);
            }
        }

        if (!filter_var($data_array["email"], FILTER_VALIDATE_EMAIL)) {
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
                tbl_users.first_name = '" . $this->ezSqlDB->escape($data_array["first_name"]) . "',
                tbl_users.last_name = '" . $this->ezSqlDB->escape($data_array["last_name"]) . "',
                tbl_users.statusID = " . (int)$data_array["statusID"] . ",
                tbl_users.user_created_datetime = SYSDATE(),
                tbl_users.user_updated_datetime = SYSDATE(),
                tbl_users.user_last_login_datetime = SYSDATE(),
                tbl_users.login_count = 0,
                tbl_users.welcome_email_sent = " . (int)$data_array["welcome_email_sent"] . ",
                tbl_users.uuid = '" . $this->SqueakyMindsPhpHelper->uuid() . "',
                tbl_users.reset_pass_uuid = NULL,
                tbl_users.user_ip = NULL";

        $this->ezSqlDB->query('BEGIN');

        $this->userID = ($this->ezSqlDB->query($query_sql) ? (int)$this->ezSqlDB->insert_id : false);

        if (!$this->userID) {
            $this->ezSqlDB->query('ROLLBACK');
            throw new Exception("Insert tbl_users failed: " . $this->ezSqlDB->MySQLFirephpGetLastMysqlError(), 10);
        }

        $query_sql = "
            SELECT * FROM tbl_users WHERE tbl_users.userID = " . (int)$this->userID . " LIMIT 1
        ";

        $current_user_row = $this->ezSqlDB->get_row($query_sql, ARRAY_A);

        if (!$current_user_row) {
            $this->ezSqlDB->query('ROLLBACK');
            throw new Exception("Get user failed: " . $this->ezSqlDB->MySQLFirephpGetLastMysqlError(), 10);
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
                        tbl_users_meta_data.key_value = '" . (string)$this->ezSqlDB->escape($dv) . "',
                        tbl_users_meta_data.userID = " . (int)$this->userID . ",
                        tbl_users_meta_data.updated_ts = SYSDATE()";

                $this->ezSqlDB->query($query_sql);
            }
        }

        // commit the queries
        if ($this->ezSqlDB->query('COMMIT') !== false) {
            // transaction was successful

            // Execute owpUDF_On_addUserSuccess user defined function
            if (function_exists("owpUDF_On_addUserSuccess")) {
                $owpUDF_On_addUserSuccess = owpUDF_On_addUserSuccess(array("userID" => (int)$this->userID, "db" => $this->ezSqlDB));
                if ($owpUDF_On_addUserSuccess) {
                    throw new Exception((string)$owpUDF_On_addUserSuccess, 30);
                }
            }

            return (int)$this->userID;
        } else {
            // transaction failed, rollback
            $this->ezSqlDB->query('ROLLBACK');
            throw new Exception("Transaction failed: " . $this->ezSqlDB->MySQLFirephpGetLastMysqlError(), 10);
        }
    }

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
        return $this->ezSqlDB->query(
            "
            UPDATE tbl_users
            SET tbl_users.statusID = " . (int)$statusID . ",
                tbl_users.reset_pass_uuid = NULL
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );
    }

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

        $this->ezSqlDB->query('BEGIN');

        $query_sql = "
            DELETE FROM tbl_users
            WHERE tbl_users.userID = " . (int)$userID . " LIMIT 1";

        $this->ezSqlDB->query($query_sql);

        $query_sql = "
            DELETE FROM tbl_users_meta_data
            WHERE tbl_users_meta_data.userID = " . (int)$userID;

        $this->ezSqlDB->query($query_sql);

        $query_sql = "
            DELETE FROM tbl_users_rights
            WHERE tbl_users_rights.userID = " . (int)$userID;

        $this->ezSqlDB->query($query_sql);

        // commit the queries
        if ($this->ezSqlDB->query('COMMIT') !== false) {
            // transaction was successful

            // Execute owpUDF_On_deleteUser user defined function
            if (function_exists("owpUDF_On_deleteUser")) {
                $owpUDF_On_deleteUser = owpUDF_On_deleteUser(array("userID" => (int)$userID, "db" => $this->ezSqlDB));
                if ($owpUDF_On_deleteUser) {
                    throw new Exception((string)$owpUDF_On_deleteUser, 30);
                }
            }

            // unset the session if the user being deleted is logged in.
            if ((int)$this->userID() === (int)$userID && isset($_SESSION["userData"])) {
                unset($_SESSION["userData"]);
                $this->PhpConsole->debug("unset", 'deleteUser->userData');
            }

            return true;
        } else {
            // transaction failed, rollback
            $this->ezSqlDB->query('ROLLBACK');

            return false;
        }
    }

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
    }

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
    }

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

        $userData = $this->ezSqlDB->get_results($query_sql);

        if ($userData) {
            foreach ($userData as $ud) {
                $response[$ud->key_name] = $ud->key_value;
            }
        }

        return $response;
    }

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
    }

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
    }

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
    }

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

        return $this->ezSqlDB->get_row($query_sql, ARRAY_A);
    }

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
        return (int)$this->ezSqlDB->get_var(
            "
            SELECT tbl_users.userID
            FROM tbl_users
            WHERE tbl_users.reset_pass_uuid = '" . (string)$reset_pass_uuid . "'
            LIMIT 1"
        );
    }

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
    public function isAdmin()
    {
        if (isset($_SESSION["userData"]) && (int)$_SESSION["userData"]["is_admin"]) {
            return true;
        } else {
            return false;
        }
    }

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
    public function isLoggedIn()
    {
        if (isset($_SESSION["userData"])) {
            return true;
        } else {
            return false;
        }
    }

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
            $owpUDF_On_logOut = owpUDF_On_logOut(array("userID" => (int)$this->userID(), "db" => $this->ezSqlDB));
            if ($owpUDF_On_logOut) {
                throw new Exception($owpUDF_On_logOut, 30);
            }
        }

        if (isset($_SESSION["userData"])) {
            unset($_SESSION["userData"]);
            $this->PhpConsole->debug("unset", 'logOut->userData');
        }

        return (boolean)isset($_SESSION["userData"]);
    }

    /**
     * refresh_user_session
     *
     * @method refresh_user_session() Refresh the session data from the user's record
     * @access public
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function refresh_user_session()
    {
        if ((int)$this->userID()) {
            $a1 = $this->get_user_record_noMeta(" WHERE tbl_ser.userID = " . (int)$this->userID());
            $a2 = $this->getUserMetaData((int)$this->userID());
            $_SESSION["userData"] = array_merge($a1, $a2);

            return true;
        }

        return false;
    }

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
        if ((int)$this->userID()) {
            $query_sql = "
                SELECT COUNT(*)
                FROM tbl_users
                WHERE tbl_users.userID = " . (int)$this->userID() . "
                LIMIT 1";

            $result = $this->ezSqlDB->get_var($query_sql);

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
        }
    }

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
        $reset_pass_uuid = $this->owp_SupportMethods->uuid();

        $this->ezSqlDB->query(
            "
            UPDATE tbl_users
            SET tbl_users.reset_pass_uuid = '" . (string)$reset_pass_uuid . "',
                tbl_users.statusID = 4
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

        return $this->get_user_info_row("WHERE tbl_users.userID = " . (int)$userID);
    }

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
        $reset_pass_uuid = $this->owp_SupportMethods->uuid();

        $this->ezSqlDB->query(
            "
            UPDATE tbl_users
            SET tbl_users.reset_pass_uuid = '" . (string)$reset_pass_uuid . "',
                tbl_users.statusID = 4
            WHERE tbl_users.email = '" . (string)$email . "'
            LIMIT 1"
        );

        return $this->get_user_info_row("WHERE LCASE(tbl_users.email) = LCASE('" . filter_var($email, FILTER_SANITIZE_EMAIL) . "')");
    }

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
        $this->ezSqlDB->query(
            "
            UPDATE tbl_users
            SET tbl_users.statusID = " . (int)$statusID . "
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

        // Execute owpUDF_On_setStatusID user defined function
        if (function_exists("owpUDF_On_setStatusID")) {
            $owpUDF_On_setStatusID = owpUDF_On_setStatusID(array("userID" => (int)$this->userID(), "db" => $this->ezSqlDB));
            if ($owpUDF_On_setStatusID) {
                throw new Exception($owpUDF_On_setStatusID, 30);
            }
        }
    }

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
    public function userData()
    {
        if ($this->isLoggedIn()) {
            return $_SESSION["userData"];
        } else {
            return false;
        }
    }

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
    public function userDataItem($item, $alternate_data = "", $append_to_item = "")
    {
        if ($this->userData() && isset($_SESSION["userData"][(string)$item])) {
            return (string)$_SESSION["userData"][(string)$item] . (strlen((string)$append_to_item) ? " " . $append_to_item : "");
        } else {
            return (string)$alternate_data . (strlen((string)$append_to_item) ? " " . $append_to_item : "");
        }
    }

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
    public function userID()
    {
        return (int)$this->userDataItem("userID", 0);
    }

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

        $this->ezSqlDB->query('BEGIN');

        $this->ezSqlDB->query(
            "
            UPDATE tbl_users
            SET tbl_users.passwd = '" . $new_hash . "'
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );

        // Execute owpUDF_On_updatePassword user defined function
        if (function_exists("owpUDF_On_updatePassword")) {
            $owpUDF_On_updatePassword = owpUDF_On_updatePassword(array("userID" => (int)$this->userID(), "db" => $this->ezSqlDB));
            if ($owpUDF_On_updatePassword) {
                throw new Exception($owpUDF_On_updatePassword, 30);
            }
        }

        if ($this->ezSqlDB->query('COMMIT') !== false) {
            return true;
        } else {
            // transaction failed, rollback
            $this->ezSqlDB->query('ROLLBACK');
            $this->errors[] = array("message" => "updatePassword: transaction failed, rollback.", "details" => array("last_mysql_error" => $this->ezSqlDB->MySQLFirephpGetLastMysqlError()));

            return false;
        }
    }

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
            $this->errors[] = array("message" => "updateUser: Invalid userID.", "details" => array("userID" => (int)$userID));

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

        $current_user_row = $this->ezSqlDB->get_row($query_sql, ARRAY_A);

        if (!$current_user_row) {
            $this->errors[] = array("message" => "updateUser: current_user_row SELECT tbl_users failed.", "details" => array("query_sql" => $query_sql, "last_mysql_error" => $this->ezSqlDB->MySQLFirephpGetLastMysqlError()));

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
                    $ck_sql_array[] = " `tbl_users`.`" . (string)$ckc . "` = '" . $this->ezSqlDB->escape((string)$data_array[$ckc]) . "' ";
                }
            }
        }

        $ck_sql_array[] = " `tbl_users`.`user_updated_datetime` = SYSDATE() ";

        $this->ezSqlDB->query('BEGIN');

        $setStatement = implode(", ", $ck_sql_array);

        $query_sql = "
                UPDATE tbl_users
                SET $setStatement
                WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1";

        $this->ezSqlDB->query($query_sql);

        if ($meta_keys && is_array($meta_keys)) {
            foreach ($meta_keys as $dk => $dv) {
                $query_sql = "
                    REPLACE INTO tbl_users_meta_data
                    SET
                        tbl_users_meta_data.key_name = '" . (string)$dk . "',
                        tbl_users_meta_data.key_value = '" . (string)$this->ezSqlDB->escape($dv) . "',
                        tbl_users_meta_data.userID = " . (int)$userID . ",
                        tbl_users_meta_data.updated_ts = SYSDATE()";

                $this->ezSqlDB->query($query_sql);
            }
        }

        // commit the queries
        if ($this->ezSqlDB->query('COMMIT') !== false) {
            // transaction was successful

            // Execute owpUDF_On_updateUser user defined function
            if (function_exists("owpUDF_On_updateUser")) {
                $owpUDF_On_updateUser = owpUDF_On_updateUser(array("userID" => (int)$userID, "db" => $this->ezSqlDB));
                if ($owpUDF_On_updateUser) {
                    throw new Exception($owpUDF_On_updateUser, 30);
                }
            }

            return true;
        } else {
            // transaction failed, rollback
            $this->ezSqlDB->query('ROLLBACK');
            $this->errors[] = array("message" => "updateUser: transaction failed, rollback.", "details" => array("last_mysql_error" => $this->ezSqlDB->MySQLFirephpGetLastMysqlError()));

            return false;
        }
    }

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

        return (boolean)((int)$this->ezSqlDB->get_var($query_sql) ? true : false);
    }

    /**
     * userLogin
     *
     * @method userLogin($email, $passwd) User login via email and password
     * @access public
     * @param  string $email  Valid user email address
     * @param  string $passwd Valid user password
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function userLogin($email, $passwd)
    {
        $exists = $this->userLoginCore("WHERE LCASE(tbl_users.email) = LCASE('" . filter_var($email, FILTER_SANITIZE_EMAIL) . "')");

        if ($exists) {
            $val_pass = $this->validate_password($passwd, $this->userDataItem("passwd"));

            if ($val_pass === true) {
                $this->PhpConsole->debug($_SESSION["userData"], 'userLogin->userData');

                return $this->userID();
            } else {
                if (isset($_SESSION["userData"])) {
                    unset($_SESSION["userData"]);
                    $this->PhpConsole->debug("unset", 'userLogin->userData');
                }
            }
        }

        return false;
    }

    /**
     * userLoginCore
     *
     * @method userLoginCore($where_statement) The core query method
     * @access private
     * @return boolean
     * @param  string $where_statement Where statement to be using within the user row query
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    private function userLoginCore($where_statement)
    {
        if (isset($_SESSION["userData"])) {
            unset($_SESSION["userData"]);
            $this->PhpConsole->debug("unset", 'userLoginCore->userData');
        }

        $get_user_info_row = $this->get_user_info_row($where_statement);

        if ($get_user_info_row) {
            $_SESSION["userData"] = $get_user_info_row;

            $this->updateLoginCount((int)$get_user_info_row["userID"]);

            // Execute owpUDF_On_userLoginCore user defined function
            if (function_exists("owpUDF_On_userLoginCore")) {
                owpUDF_On_userLoginCore(array("userID" => (int)$get_user_info_row["userID"], "db" => $this->ezSqlDB));
            }

            return true;
        } else {
            return false;
        }
    }

    /**
     * userLoginViaToken
     *
     * @method userLoginViaToken($uuid) User login via UUID token
     * @access public
     * @param  string $uuid User UUID token created with the record, not related to the lost password UUID
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function userLoginViaToken($uuid)
    {
        return $this->userLoginCore("WHERE tbl_users.uuid = LCASE('" . $uuid . "')");
    }

    /**
     * userLoginViaUserID
     *
     * @method userLoginViaUserID($userID) User login via UserID
     * @access public
     * @param  int $userID Existing userID
     * @return boolean
     *
     * @author  Brian Tafoya <btafoya@briantafoya.com>
     * @version 1.0
     */
    public function userLoginViaUserID($userID)
    {
        return $this->userLoginCore("WHERE tbl_users.userID = " . (int)$userID);
    }

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
        $this->ezSqlDB->query(
            "
            UPDATE tbl_users
            SET tbl_users.login_count = tbl_users.login_count + 1,
                tbl_users.user_last_login_datetime = SYSDATE(),
                tbl_users.user_ip = '" . OwpSupportMethods::GetUserIP() . "'
            WHERE tbl_users.userID = " . (int)$userID . "
            LIMIT 1"
        );
    }

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
        $this->ezSqlDB->query(
            "
            REPLACE INTO tbl_users_rights
            SET tbl_users_rights.userID = " . (int)$userID . ",
                tbl_users_rights.is_admin = " . (int)$is_admin . ",
                tbl_users_rights.hide_ads = " . (int)$hide_ads . ",
                tbl_users_rights.is_dev = " . (int)$is_dev . "
            "
        );
    }

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
    }

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
        return (bool)$this->ezSqlDB->get_var(
            "
            SELECT COUNT(*) FROM tbl_users_status
            WHERE tbl_users_status.statusID = " . (int)$statusID . "
            LIMIT 1"
        );
    }

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
    }
}
