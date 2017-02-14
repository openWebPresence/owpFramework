<?php
/**
 * Open Web Presence - openwebpresence.com
 *
 * Copyright (c) 2017, Brian Tafoya
 *
 * @author Brian Tafoya <btafoya@briantafoya.com>
 * @copyright 2001 - 2017, Brian Tafoya.
 * @license http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @note This library is distributed in the hope that it will be useful - WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Open Web Presence phpMailer Extension Class
 *
 * @author     Brian Tafoya
 * @version    1.0
 */

class owp_Messaging {

	public $errors = false;

	/**
	 * sendEmailDirect
	 *
	 * @method sendEmailDirect()
	 * @access public
	 * @return boolean
	 *
	 * @author     Brian Tafoya
	 * @version    1.0
	 */
	public function sendEmailDirect($data_array) {

		if($this->validateData($data_array)) {
			throw new Exception($this->errors);
		}

		$email_to_clean = filter_var($data_array["email_to"], FILTER_SANITIZE_EMAIL);

		$mail = new PHPMailer;
		$mail->IsSMTP();

		list($to_addy_info) = imap_rfc822_parse_adrlist($email_to_clean,"");
		$dns_get_mx = dns_get_record($to_addy_info->host,DNS_MX);
		$ip = gethostbyname($dns_get_mx[0]["target"]);

		$mail->Host = $ip;

		$mail->XMailer = "OpenWebPresence-1.0";
		$mail->Helo = $data_array["sender_domain"];
		$mail->Hostname = $data_array["sender_domain"];

		$mail->From = $data_array["email_from"];
		$mail->FromName = $data_array["email_from_name"];
		$mail->ReturnPath = $data_array["reply_to"];
		$mail->AddAddress($email_to_clean, $data_array["email_to_name"]);
		$mail->AddReplyTo(filter_var($data_array["reply_to"], FILTER_SANITIZE_EMAIL));

		$mail->DKIM_domain = $_ENV["DKIM_domain"];
		$mail->DKIM_private = ROOT_PATH . 'PHPMailer_DKIM/' . $_ENV["DKIM_private"] . '.htkeyprivate';
		$mail->DKIM_selector = $_ENV["DKIM_selector"];
		$mail->DKIM_passphrase = $_ENV["DKIM_passphrase"];
		$mail->DKIM_identity = $_ENV["DKIM_identity"];

		$mail->WordWrap = 50;
		$mail->IsHTML(true);

		$mail->Subject = $data_array["subject"];
		$mail->Body    = $data_array["message_body"];

		$mail->addCustomHeader("X-AntiAbuse" , "This is a solicited email for " . $data_array["sender_domain"]. ".");
		$mail->addCustomHeader("X-AntiAbuse" , $data_array["email_from"]);

		$message_sent = (int)($mail->Send()?1:0);

		return $message_sent;
	}


	/**
	 * sendEmailViaSMTP
	 *
	 * @method sendEmailViaSMTP()
	 * @access public
	 * @return boolean
	 *
	 * @author     Brian Tafoya
	 * @version    1.0
	 */
	public function sendEmailViaSMTP($data_array) {

		if($this->validateData($data_array)) {
			throw new Exception($this->errors);
		}

		$email_to_clean = filter_var($data_array["email_to"], FILTER_SANITIZE_EMAIL);

		$mail = new PHPMailer;

		$mail->isSMTP();
		$mail->Host = (string)$_ENV["smtp_hostname"];
		$mail->SMTPAuth = (bool)$_ENV["smtp_hostname"];
		if((bool)$_ENV["smtp_hostname"]) {
			$mail->Username = (string)$_ENV["smtp_username"];
			$mail->Password = (string)$_ENV["smtp_password"];
		}
		if((string)$_ENV["smtp_secure"] != "none") {
			$mail->SMTPSecure = (string)$_ENV["smtp_secure"];
		}
		$mail->Port = (string)$_ENV["smtp_port"];

		$mail->XMailer = "OpenWebPresence-1.0";
		$mail->Helo = $data_array["sender_domain"];
		$mail->Hostname = $data_array["sender_domain"];

		$mail->From = $data_array["email_from"];
		$mail->FromName = $data_array["email_from_name"];
		$mail->ReturnPath = $data_array["reply_to"];
		$mail->AddAddress($email_to_clean, $data_array["email_to_name"]);
		$mail->AddReplyTo(filter_var($data_array["reply_to"], FILTER_SANITIZE_EMAIL));

		$mail->DKIM_domain = $_ENV["DKIM_domain"];
		$mail->DKIM_private = ROOT_PATH . 'PHPMailer_DKIM/' . $_ENV["DKIM_private"] . '.htkeyprivate';
		$mail->DKIM_selector = $_ENV["DKIM_selector"];
		$mail->DKIM_passphrase = $_ENV["DKIM_passphrase"];
		$mail->DKIM_identity = $_ENV["DKIM_identity"];

		$mail->WordWrap = 50;
		$mail->IsHTML(true);

		$mail->Subject = $data_array["subject"];
		$mail->Body    = $data_array["message_body"];

		$mail->addCustomHeader("X-AntiAbuse" , "This is a solicited email for " . $data_array["sender_domain"]. ".");
		$mail->addCustomHeader("X-AntiAbuse" , $data_array["email_from"]);

		$message_sent = (int)($mail->Send()?1:0);

		return $message_sent;
	}


	/**
	 * isDev
	 *
	 * @method isDev()
	 * @access private
	 * @return boolean
	 *
	 * @author     Brian Tafoya
	 * @version    1.0
	 */
	private function isDev() {
		return ((int)$_ENV["ISDEV"]?true:false);
	}


	/**
	 * validateData
	 *
	 * @method validateData()
	 * @access private
	 * @return boolean
	 *
	 * @author     Brian Tafoya
	 * @version    1.0
	 */
	private function validateData($data_array) {
		$required_variables = array("sender_domain", "subject", "message_body", "email_to", "email_to_name", "email_from", "email_from_name", "reply_to");

		$missing_columns = array_diff($required_variables, array_keys($data_array));

		if($missing_columns) {
			$this->errors[] = array("message"=>"The following columns are missing.","details"=>$missing_columns);
		}

		return ($this->errors?false:true);
	}
}

