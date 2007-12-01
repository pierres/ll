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
class Io extends Modul{

private $contentType 	= 'Content-Type: text/html; charset=UTF-8';
private $status		= 'HTTP/1.1 200 OK';

const NOT_FOUND		= 'HTTP/1.1 404 Not Found';
const OK		= 'HTTP/1.1 200 OK';
const FOUND		= 'HTTP/1.1 302 Found';

private $request 	= array();


function __construct()
	{
	$this->request = &$_REQUEST;

	if (get_magic_quotes_gpc() == 1 || get_magic_quotes_runtime() == 1)
		{
		die('"magic_quotes_gpc" oder "get_magic_quotes_runtime" ist aktiviert!');
		}

	if (strpos($this->getEnv('HTTP_ACCEPT'), 'application/xhtml+xml') !== false)
		{
		$this->contentType = 'Content-Type: application/xhtml+xml; charset=UTF-8';
		}
	}
/** FIXME: XSS->alle Zeilenumbrüche entfernen */
private function header($string)
	{
	if (@header($string) != 0)
		{
		throw new IoException($string);
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

public function setCookie($key, $value, $expire = 0)
	{
	setcookie($key, $value, $expire, '', '', getenv('HTTPS'), true);
	}

public function out(&$text)
	{
	$this->header ($this->status);
	$this->header ($this->contentType);

	if (strpos($this->getEnv('HTTP_ACCEPT_ENCODING'), 'gzip') !== false)
		{
		$this->header('Content-Encoding: gzip');
		$this->header('Vary: Accept-Encoding');
		$text = gzencode($text, 3);
		}

	$this->header('Content-Length: '.strlen($text));
	echo $text;

	exit();
	}

public function getHtml($name)
	{
	return htmlspecialchars($this->getString($name), ENT_COMPAT);
	}

public function isRequest($name)
	{
	return isset($this->request[$name]);
	}

public function isEmpty($name)
	{
	return !$this->isRequest($name) || (strlen($this->request[$name]) == 0);
	}

public function isEmptyString($name)
	{
	$this->request[$name] = trim($this->request[$name]);
	return empty($this->request[$name]);
	}
/**
* FIXME: Prüfe Seiteneffekt bei leerem Rückgabewert
*/
public function getString($name)
	{
	if (isset($this->request[$name]))
		{
		/** FIXME: trim wird hier evtl. nicht erwartet! */
		$this->request[$name] = trim($this->request[$name]);
		return $this->request[$name];
		}
	else
		{
		throw new IoRequestException($name);
		}
	}

public function getEnv($name)
	{
	return getenv($name);
	}

/**
* liefert immer einen Integer-Wert
* @param $name Name es Parameters
*/
public function getInt($name)
	{
	return intval($this->getString($name));
	}

/**
* liefert immer einen Hex-Wert
* @param $name Name es Parameters
*/
public function getHex($name)
	{
	return hexVal($this->getString($name));
	}

public function getArray($name)
	{
	if(isset($this->request[$name]) && is_array($this->request[$name]))
		{
		return $this->request[$name];
		}
	else
		{
		throw new IoRequestException($name);
		}
	}

public function getLength($name)
	{
	return (empty($this->request[$name]) ? 0 : strlen($this->getString($name)));
	}
/** FIXME: XSS->alle Zeilenumbrüche entfernen */
public function redirect($class, $param = '', $id = 0)
	{
	$param = (!empty($param) ? ';'.$param : '');

	$this->header (Io::FOUND);
	$this->redirectToUrl($this->getURL().'?id='.($id == 0 ? $this->Board->getId() : $id).';page='.$class.$param);
	}

public function getHost()
	{
	return getenv('HTTP_HOST');
	}

/** TODO: ist dieser Rückgabewert vom Nutzer manipulierbar? */
public function getURL()
	{
	return 'http'.(!getenv('HTTPS') ? '' : 's').'://'
			.getenv('HTTP_HOST')
			.dirname($_SERVER['PHP_SELF']);
	}
/** FIXME: XSS->alle Zeilenumbrüche entfernen */
public function redirectToUrl($url)
	{
	$this->header (Io::FOUND);
	$this->header('Location: '.$url);
	exit();
	}

private function curlInit($url)
	{
	if (!preg_match('/^(https?|ftp):\/\//', $url))
		{
 		throw new IoException('Nur http und ftp-Protokolle erlaubt');
		}

	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
	curl_setopt($curl, CURLOPT_TIMEOUT, 5);
	curl_setopt($curl, CURLOPT_ENCODING, '');

	return $curl;
	}

public function getRemoteFileSize($url)
	{
	$curl = $this->curlInit($url);
	curl_setopt($curl, CURLOPT_NOBODY, true);
	curl_exec($curl);
	$size = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
	curl_close($curl);

	return $size;
	}

public function getRemoteFile($url)
	{
	$curl = $this->curlInit($url);
	$content = curl_exec($curl);
// 	$ype = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
	curl_close($curl);

	$type = getTypeFromContent($content);

	if (!$this->isAllowedType($type))
		{
		throw new IoMimeException('Dateien des Typs <strong>'.htmlspecialchars($type).'</strong> dürfen nicht hochgeladen werden! Folgende Typen sind erlaubt:<ul><li>'.implode('</li><li>', $this->Settings->getValue('allowed_mime_types')).'</li></ul>');
		}

	return array('type' => $type, 'content' => $content);
	}

public function getUploadedFile($name)
	{
	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name']))
		{
		$type = getTypeFromFile($_FILES[$name]['tmp_name']);

		if (!$this->isAllowedType($type))
			{
			throw new IoMimeException('Dateien des Typs <strong>'.htmlspecialchars($type).'</strong> dürfen nicht hochgeladen werden! Folgende Typen sind erlaubt:<ul><li>'.implode('</li><li>', $this->Settings->getValue('allowed_mime_types')).'</li></ul>');
			}

		$_FILES[$name]['type'] = $type;
		return $_FILES[$name];
		}
	elseif (isset($_FILES[$name]) && $_FILES[$name]['error'] > 0 && !empty($_FILES[$name]['name']))
		{
		switch ($_FILES[$name]['error'])
			{
			case 1 : $message = 'Die Datei ist größer als '.ini_get('upload_max_filesize').'Byte.'; break;
			case 2 : $message = 'Die Datei ist größer als der im Formular angegebene Wert.'; break;
			case 3 : $message = 'Die Datei wurde nur teilweise hochgeladen.'; break;
			case 4 : $message = 'Keine Datei empfangen.'; break;
			case 6 : $message = 'Kein temporäres Verzeichnis gefunden.'; break;
			case 7 : $message = 'Konnte Datei nicht speichern.'; break;
			case 8 : $message = 'Datei wurde von einer Erweiterung blockiert.'; break;
			default : $message = 'Unbekannter Fehler. Code: '.$_FILES[$name]['error'];
			}
			throw new IoFileSizeException('Datei wurde nicht hochgeladen! '.$message);
		}
	else
		{
		throw new IoException('Datei wurde nicht hochgeladen!');
		}
	}

private function isAllowedType($type)
	{
	foreach ($this->Settings->getValue('allowed_mime_types') as $allowedType)
		{
		// prüfe keine exakte Übereinstimmung
		if (strpos($type, $allowedType) === 0)
			{
			return true;
			}
		}

	return false;
	}

}


class IoException extends RuntimeException{


function __construct($message)
	{
	parent::__construct($message, 0);
	}

}

class IoRequestException extends IoException{


function __construct($message)
	{
	parent::__construct('Der Parameter "'.$message.'" wurde nicht übergeben.');
	}

}

class IoMimeException extends IoException{}

class IoFileSizeException extends IoException{}

?>
