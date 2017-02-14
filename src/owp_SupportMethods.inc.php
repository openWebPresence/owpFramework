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
 * Open Web Presence Support Methods Class
 *
 * @author     Brian Tafoya
 * @version    1.0
 */
class owp_SupportMethods {
	/**
	 * uuid
	 *
	 * @method uuid()
	 * @access public
	 * @return string
	 *
	 * @author     Brian Tafoya
	 * @version    1.0
	 */
	public function uuid(){
		return md5(uniqid(rand()+MicroTime(),1));
	}


	/**
	 * randomPasswordAlphaNum
	 *
	 * @method randomPasswordAlphaNum()
	 * @access public
	 * @return string
	 *
	 * @author     Brian Tafoya
	 * @version    1.0
	 */
	public function randomPasswordAlphaNum( $length ) {
		$alphabets = range('A','Z');
		$alphabets_lower = range('a','z');
		$numbers = range('0','9');
		$final_array = array_merge($alphabets,$numbers,$alphabets_lower);

		$password = '';

		while($length--) {
			$key = array_rand($final_array);
			$password .= $final_array[$key];
		}

		return (string)$password;
	}
}