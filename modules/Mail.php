<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class Mail extends Modul {

private $from 		= '';
private $to 		= '';
private $replyto 	= '';
private $text 		= '';
private $subject 	= '';


public function send()
	{
	$logDir = $this->Settings->getValue('mail_log_dir');
	if (!empty($logDir))
		{
		$log = 	$this->from."\n".
			$this->to."\n".
			$this->subject."\n".
			$this->text."\n";
		file_put_contents($logDir.'/'.time().'.txt', $log);
		}

	mail($this->to, mb_convert_encoding($this->subject, 'ISO-8859-1', 'UTF-8'), mb_convert_encoding($this->text, 'ISO-8859-1', 'UTF-8'), 'From: '.$this->from."\n".(!empty($this->replyto) ? 'Reply-To: '.$this->replyto."\n" : ''), '-f'.$this->from);
	}

public function setFrom($from)
	{
	$this->from = $from;
	}

public function setTo($to)
	{
	$this->to = $to;
	}

public function setReplyTo($addess)
	{
	$this->replyto = $addess;
	}

public function setSubject($subject)
	{
	$this->subject = $subject;
	}

public function setText($text)
	{
	$this->text = $text;
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
