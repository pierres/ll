<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class Mail extends Modul{

private $from 		= '';
private $to 		= '';
private $replyto 	= '';
private $text 		= '';
private $subject 	= '';


public function send()
	{
	$headers =
		"MIME-Version: 1.0\n".
		"Content-type: text/plain; charset=\"utf-8\"\n".
		"Content-Transfer-Encoding: quoted-printable\n".
		'From: '.$this->from;
	if (!empty($this->replyto))
		{
		$headers .= "\nReply-To: ".$this->replyto;
		}

	$logDir = $this->Settings->getValue('mail_log_dir');
	if (!empty($logDir))
		{
		$log = 	$headers."\n".
			$this->to."\n".
			$this->subject."\n".
			$this->text."\n";
		file_put_contents($logDir.'/'.time().'.txt', $log);
		}

	if (mail($this->to, $this->subject, $this->text, $headers) === false)
		{
		throw new MailException('Error sending mail');
		}
	}

# inspired by MediaWiki
private function escapeCharacters($matches)
	{
	return sprintf('=%02X', ord($matches[1]));
	}
private function quotePrintable($text)
	{
	$illegal = '\x00-\x08\x0b\x0c\x0e-\x1f\x7f-\xff=';
	$replace = $illegal . '\t ?_';

	if (!preg_match("/[$illegal]/", $text))
		{
		return $text;
		}

	$out = '=?UTF-8?Q?';
	$out .= preg_replace( "/([$replace])/e", 'sprintf("=%02X",ord("$1"))', $text);
	$out .= preg_replace_callback("/([$replace])/", array($this, 'escapeCharacters'), $text);
	$out .= '?=';

	return $out;
	}

public function setFrom($from)
	{
	$this->from = $this->quotePrintable($from);
	}

public function setTo($to)
	{
	$this->to = $this->quotePrintable($to);
	}

public function setReplyTo($addess)
	{
	$this->replyto = $this->quotePrintable($addess);
	}

public function setSubject($subject)
	{
	$this->subject = $this->quotePrintable($subject);
	}

public function setText($text)
	{
	$this->text = $this->quotePrintable($text);
	}

public function validateMail($mail)
	{
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*)?';
	$tld 		= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;

	return (boolean) preg_match('/^'.$name.'@'.$domain.'$/Di', $mail);
	}

}

class MailException extends RuntimeException{

}


?>