<?php


class Mail extends Modul{

private $from 		= '';
private $to 		= '';
private $text 		= '';
private $subject 	= '';


public function send()
	{
	mail($this->to, $this->subject, $this->text, 'Content-Type: text/plain;charset="UTF-8"'."\r\n".'From: '.$this->from."\r\n");
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
	$this->subject = $this->encodeMimeSubject($subject);
	}

public function setText($text)
	{
	$this->text = $text;
	}

public function validateMail($mail)
	{
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*[a-z0-9])?';
	$tld 			= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;

	return (boolean) preg_match('/^'.$name.'@'.$domain.'$/i', $mail);
	}

private function encodeMimeSubject($s)
	{
	$lastspace=-1;
	$r="";
	$buff="";

	$mode=1;

	for ($i=0; $i<strlen($s); $i++) {
		$c=substr($s,$i,1);
		if ($mode==1) {
		$n=ord($c);
		if ($n & 128) {
			$r.="=?UTF-8?Q?";
			$i=$lastspace;
			$mode=2;
		} else {
			$buff.=$c;
			if ($c==" ") {
			$r.=$buff;
			$buff="";
			$lastspace=$i;
			}
		}
		} else if ($mode==2) {
		$r.=$this->qpchar($c);
		}
	}
	if ($mode==2) $r.="?=";

	return $r;
	}

private function qpchar($c)
	{
	$n=ord($c);
	if ($c==" ") return "_";
	if ($n>=48 && $n<=57) return $c;
	if ($n>=65 && $n<=90) return $c;
	if ($n>=97 && $n<=122) return $c;
	return "=".($n<16 ? "0" : "").strtoupper(dechex($n));
	}

}


?>