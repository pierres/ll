<?php

/** TODO \n in Mail einschleussbar? http://forum.hardened-php.net/viewtopic.php?id=69 */
class Mail extends Modul{

private $from 		= '';
private $to 		= '';
private $replyto 		= '';
private $text 		= '';
private $subject 	= '';

/** FIXME: XSS->alle Zeilenumbrüche entfernen */
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

	mb_internal_encoding('UTF-8');
	mb_language('uni');
	mb_send_mail($this->to, $this->subject, $this->text, 'From: '.$this->from."\r\n".(!empty($this->replyto) ? 'Reply-To: '.$this->replyto."\r\n" : ''));
	}

public function setFrom($from)
	{
// 	if ($this->validateMail($from))
// 		{
		$this->from = $from;
// 		}
// 	else
// 		{
// 		throw new MailException('keine gültige Mail-Adresse', 0);
// 		}
	}

public function setTo($to)
	{
// 	if ($this->validateMail($to))
// 		{
		$this->to = $to;
// 		}
// 	else
// 		{
// 		throw new MailException('keine gültige Mail-Adresse', 0);
// 		}
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
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*[a-z0-9])?';
	$tld 		= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;

	return (boolean) preg_match('/^'.$name.'@'.$domain.'$/Di', $mail);
	}

}

class MailException extends RuntimeException{

}


?>