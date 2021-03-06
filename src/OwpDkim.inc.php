<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpDkim
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
 * DKIM generation and use class.
 */
class OwpDkim
{


    /**
     * createDkimRecords
     *
     * @method createDkimRecords($domain, $file_path)
     * @access public
     * @param  $domain
     * @param  string $file_path
     * @param  string $selector
     * @return array Dkim instructions will be output.
     * @throws Exception Unable to create the DKIM directory.
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static public function createDkimRecords($domain, $file_path = "", $selector = "owp")
    {

        if($file_path) {
            $throw = self::createPath($file_path);

            if(!$throw) {
                throw new Exception("Unable to create the DKIM directory; ".$file_path);
            }
        }

        $files = self::keyFileNamePath($domain);

        $keys = self::generateKeys();

        $dkim_selector = (string)$selector . '._domainkey';

        $dkim_record = 'v=DKIM1; k=rsa; p='.str_replace(array("-----BEGIN PUBLIC KEY-----", "-----END PUBLIC KEY-----", "\n"), "", $keys["publickey"]);

        $spf_text = 'v=spf1 a mx a:'.$domain.' ~all';

        if($file_path) {
            file_put_contents($file_path.$files["public_key_filename"], $keys["publickey"]);
            file_put_contents($file_path.$files["private_key_filename"], $keys["privatekey"]);
        }

        return self::generateInstructions($domain, $dkim_selector, $dkim_record, $spf_text, $keys, $file_path);

    }//end createDkimRecords()


    /**
     * validateDkim
     *
     * @method validateDkim($domain, $file_path)
     * @access public
     * @param  string $domain    The domain to generate DKIM keys.
     * @param  string $file_path File path used to store the keys and instructions.
     * @return boolean Dkim exist.
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static public function validateDkim($domain, $file_path)
    {

        $files = self::keyFileNamePath($domain);

        if(file_exists($file_path.$files["public_key_filename"]) && file_exists($file_path.$files["private_key_filename"])) {
            return true;
        } else {
            ob_clean();
            echo self::createDkimRecords($domain, $file_path);
            die();
        }

    }//end validateDkim()


    /**
     * keyFileNamePath
     *
     * @method keyFileNamePath()
     * @access public
     * @param  string $domain Domain to generate filename for.
     * @return array
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static public function keyFileNamePath($domain)
    {
        $files = array(
            "public_key_filename"  => $domain.'.htkeypublic_',
            "private_key_filename" => $domain.'.htkeyprivate',
        );

        return $files;

    }//end keyFileNamePath()


    /**
     * generateKeys
     *
     * @method generateKeys()
     * @access private
     * @return array
     *
     * @author  Brian Tafoya
     * @version 1.0
     */
    static protected function generateKeys()
    {

        $config = array(
            "digest_alg"       => "sha256",
            "private_key_bits" => 1024,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );

        // Create the keypair
        $res = openssl_pkey_new($config);

        // Get private key
        openssl_pkey_export($res, $privatekey);

        // Get public key
        $publickey = openssl_pkey_get_details($res);

        return array(
            "privatekey" => (string) $privatekey,
            "publickey"  => (string) $publickey["key"],
        );

    }//end generateKeys()


    /**
     * generateInstructions
     *
     * @author  Brian Tafoya
     * @version 1.0
     *
     * @param $domain
     * @param $dkim_selector
     * @param $dkim_record
     * @param $spf_record
     * @param $keys
     * @param $file_path
     *
     * @return array
     */
    static private function generateInstructions($domain, $dkim_selector, $dkim_record, $spf_record, $keys, $file_path)
    {

        $instructions = "
DKIM keys and record information has been generated for the domain ".$domain.".\n
\n
DNS Records are as follows:\n
- Record Type: TXT - Host Name: ".$dkim_selector.".".$domain." - Text: ".$dkim_record."\n
- Record Type: TXT - Host Name: blank - Text: ".$spf_record."\n
\n\n
For your records:\n
_Private Key_\n\n
".$keys["privatekey"]."\n\n
_Public Key_\n\n
".$keys["publickey"]."\n\n

All of the above information has been recorded in the below file path:\n
".$file_path."\n\n\n";

        file_put_contents($file_path."instructions.txt", $instructions);

        return array(""=>$instructions, "DKIM_domain"=>$domain, "DKIM_selector"=>$dkim_selector, "DKIM_record"=>$dkim_record, "SPF_record"=>$spf_record, "DKIM_keys"=>$keys, "file_path"=>$file_path);

    }//end generateInstructions()


    /**
     * Recursively create a long directory path
     *
     * @method createPath()
     * @access private
     * @param  string $path Path to create.
     * @return boolean
     */
    static private function createPath($path)
    {
        if (is_dir($path)) return true;
        $prev_path = substr($path, 0, (strrpos($path, '/', -2) + 1));
        $return    = self::createPath($prev_path);
        return ($return && is_writable($prev_path)) ? mkdir($path) : false;

    }//end createPath()


}//end class
