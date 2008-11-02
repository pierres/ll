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

class Output extends Modul {

private $contentType 	= 'Content-Type: text/html; charset=UTF-8';
private $status		= 'HTTP/1.1 200 OK';

const NOT_FOUND		= 'HTTP/1.1 404 Not Found';
const OK		= 'HTTP/1.1 200 OK';
const FOUND		= 'HTTP/1.1 302 Found';


function __construct()
	{
	try
		{
		if (strpos($this->Input->Server->getString('HTTP_ACCEPT'), 'application/xhtml+xml') !== false)
			{
			$this->contentType = 'Content-Type: application/xhtml+xml; charset=UTF-8';
			}
		}
	catch (RequestException $e)
		{
		}
	}

public function setStatus($code)
	{
	$this->status = $code;
	}

public function setContentType($type)
	{
	$this->contentType = $type;
	}

public function setOutputHandler($handler)
	{
	$this->outputHandler = $handler;
	}

/** FIXME: XSS->alle Zeilenumbrüche entfernen */
private function writeHeader($string)
	{
	if (@header($string) != 0)
		{
		throw new OutputException($string);
		}
	}

public function setCookie($key, $value, $expire = 0)
	{
	setcookie($key, $value, $expire, '', '', $this->Input->Server->isValid('HTTPS'), true);
	}

public function writeOutput(&$text)
	{
	$this->writeHeader ($this->status);
	$this->writeHeader ($this->contentType);

	try
		{
		if (strpos($this->Input->Server->getString('HTTP_ACCEPT_ENCODING'), 'gzip') !== false)
			{
			$this->writeHeader('Content-Encoding: gzip');
			$this->writeHeader('Vary: Accept-Encoding');
			$text = gzencode($text, 3);
			}
		}
	catch (RequestException $e)
		{
		}

	$this->writeHeader('Content-Length: '.strlen($text));
	echo $text;

	exit();
	}

public function redirect($class, $param = '', $id = 0)
	{
	$param = (!empty($param) ? ';'.$param : '');

	$this->writeHeader (Output::FOUND);
	$this->redirectToUrl($this->Input->getURL().'?id='.($id == 0 ? $this->Board->getId() : $id).';page='.$class.$param);
	}

/** FIXME: XSS->alle Zeilenumbrüche entfernen */
public function redirectToUrl($url)
	{
	$this->writeHeader (Output::FOUND);
	$this->writeHeader('Location: '.$url);
	exit();
	}

}

class OutputException extends RuntimeException{


function __construct($message)
	{
	parent::__construct($message, 0);
	}

}

?>