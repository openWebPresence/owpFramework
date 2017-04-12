<?php
/**
 * OpenWebPresence Support Library - openwebpresence.com
 *
 * @copyright 2001 - 2017, Brian Tafoya.
 * @package   OwpPostmark
 * @author    Brian Tafoya <btafoya@briantafoya.com>
 * @version   1.0
 * @license   MIT
 * @license   https://opensource.org/licenses/MIT The MIT License
 * @category  OpenWebPresence_Support_Library
 * @link      http://openwebpresence.com OpenWebPresence
 * @link      https://postmarkapp.com Postmarkapp
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
 * This class provides Postmarkapp Functionality that just plain works.
 */
class OwpPostmark
{

    /**
     * @var string Postmarkapp API Key
     */
    private $api_key;

    /**
     * @var int Attachment Counter
     */
    private $attachment_count = 0;

    /**
     * @var array Data array
     */
    private $data = array();

    /**
     * OwpPostmark constructor.
     *
     * @method mixed __construct()
     * @access public
     * @param  $key
     * @param  $from
     * @param  string $reply
     */
    public function __construct($key, $from, $reply = '')
    {
        $this->api_key = $key;
        $this->data['From'] = $from;
        $this->data['ReplyTo'] = $reply;
    }

    /**
     * Send method
     *
     * @method void send() Send method
     * @access public
     * @return bool
     */
    public function send()
    {
        $client = new GuzzleHttp\Client();

        $response    = $client->request(
            'POST', "https://api.postmarkapp.com/email",
            [
                'http_errors' => false,
                'verify' => false,
                'connect_timeout' => 5,
                'timeout' => 30,
                'headers' => [
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                    'X-Postmark-Server-Token' => $this->api_key
                ],
                'body' => json_encode($this->data)
            ]
        );

        if($response->getStatusCode() != 200) {
            return false;
        }

        if($response->getBody()) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Set Send To Method
     *
     * @method mixed to($to) Set Send To Method
     * @access public
     * @param  $to
     * @return $this
     */
    public function to($to)
    {
        $this->data['To'] = $to;
        return $this;
    }

    /**
     * Set CC To Method
     *
     * @method mixed cc($cc) Set CC To Method
     * @access public
     * @param  $cc
     * @return $this
     */
    public function cc($cc)
    {
        $this->data["Cc"] = $cc;
        return $this;
    }

    /**
     * Set BCC To Method
     *
     * @method mixed bcc($bcc) Set BCC To Method
     * @access public
     * @param  $bcc
     * @return $this
     */
    public function bcc($bcc)
    {
        $this->data["Bcc"] = $bcc;
        return $this;
    }

    /**
     * Set Subject Method
     *
     * @method mixed subject($subject) Set Subject Method
     * @access public
     * @param  $subject
     * @return $this
     */
    public function subject($subject)
    {
        $this->data['Subject'] = $subject;
        return $this;
    }

    /**
     * Set HTML Message Method
     *
     * @method mixed html_message($html) Set HTML Message Method
     * @access public
     * @param  $html
     * @return $this
     */
    public function html_message($html)
    {
        $this->data['HtmlBody'] = $html;
        return $this;
    }

    /**
     * Set PLAIN Text Message Method
     *
     * @method mixed plain_message($msg) Set PLAIN Text Message Method
     * @access public
     * @param  $msg
     * @return $this
     */
    public function plain_message($msg)
    {
        $this->data['TextBody'] = $msg;
        return $this;
    }

    /**
     * Set Tag Method
     *
     * @method mixed tag($tag) Set Tag Method
     * @access public
     * @param  $tag
     * @return $this
     */
    public function tag($tag)
    {
        $this->data['Tag'] = $tag;
        return $this;
    }

    /**
     * Add Attachment Method
     *
     * @method mixed attachment($name, $content, $content_type) Add Attachment Method
     * @access public
     * @param  $name
     * @param  $content
     * @param  $content_type
     * @return $this
     */
    public function attachment($name, $content, $content_type)
    {
        $this->data['Attachments'][$this->attachment_count]['Name']         = $name;
        $this->data['Attachments'][$this->attachment_count]['ContentType']  = $content_type;

        // Check if our content is already base64 encoded or not
        if(! base64_decode($content, true))
            $this->data['Attachments'][$this->attachment_count]['Content']  = base64_encode($content);
        else
            $this->data['Attachments'][$this->attachment_count]['Content']  = $content;

        // Up our attachment counter
        $this->attachment_count++;

        return $this;
    }
}