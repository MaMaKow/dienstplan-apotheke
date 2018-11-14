<?php

/*
 * Copyright (C) 2018 Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Maybe I will write a class to handle emails...
 *
 * @see http://www.faqs.org/rfcs/rfc2822.html
 * @author Dr. rer. nat. M. Mandelkow <netbeans-pdr@martin-mandelkow.de>
 */
class email {

    /**
     * End of line defined by RFC 5322
     */
    const EMAIL_EOL = "\r\n";

    public $recipient;
    public $subject;
    public $header;
    public $header_from;
    public $header_reply_to;
    public $message_text;
    public $message_html;
    public $message_attachment;

    public function __construct() {
        throw new BadMethodCallException('This method is not implemented yet. I am sorry!');
    }

    public function set_recipient(string $recipient) {
        $this->recipient = $recipient;
    }

    public function set_subject(string $subject) {
        $this->subject = $subject;
    }

    public function create_header($from, $reply_to) {
        throw new BadMethodCallException('This method is not implemented yet. I am sorry!');
        $header = '';
        $header .= 'From: ' . $config['contact_email'] . self::EMAIL_EOL;
        $header .= 'Reply-To: ' . $config['contact_email'] . self::EMAIL_EOL;
        $header .= "MIME-Version: 1.0" . self::EMAIL_EOL;
        $header .= 'X-Mailer: PHP/' . phpversion() . self::EMAIL_EOL;
        //$header .= "Content-type: text/plain; charset=UTF-8;" . self::EMAIL_EOL;
        $random_hash = md5(time());
        $header .= 'Content-Type: multipart/mixed; boundary="' . $random_hash . '"' . self::EMAIL_EOL . self::EMAIL_EOL;
    }

    public function add_attachment(string $filename, $content) {
        throw new BadMethodCallException('This method is not implemented yet. I am sorry!');
        $attachment_content = chunk_split(base64_encode($content));
    }

    public function send() {
        throw new BadMethodCallException('This method is not implemented yet. I am sorry!');
        $mail_result = (bool) mail($to, $subject, $message, $additional_headers, $additional_parameters);
        return $mail_result;
    }

}
