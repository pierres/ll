<?php


class Mail extends Modul{

private $from 		= '';
private $to 		= '';
private $text 		= '';
private $subject 	= '';


public function send()
	{
	mb_internal_encoding('UTF-8');
	mb_language('uni');
	mb_send_mail($this->to, $this->subject, $this->text, 'From: '.$this->from."\r\n");
	}

public function setFrom($from)
	{
	$this->from = $from;
	}

public function setTo($to)
	{
	$this->to = $to;
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

	return (boolean) preg_match('/^'.$name.'@'.$domain.'$/i', $mail);
	}

}


?>