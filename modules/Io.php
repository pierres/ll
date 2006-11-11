<?php

/**
* Alle Ein- und Ausgaben laufen ber diese Klasse
*
* @author Pierre Schmitz
*/
class Io extends Modul{


private $outputHandler 	= 'ob_gzhandler';
private $contentType 	= 'Content-Type: text/html; charset=UTF-8';
private $status		= 'HTTP/1.1 200 OK';

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
/** FIXME: XSS->alle Zeilenumbrüche entfernen */
public function setCookie($key, $value, $expire = 0)
	{
	setcookie($key, $value, $expire, '', '', getenv('HTTPS'), true);
	}

public function out(&$text)
	{
	ob_start($this->outputHandler);
	$this->header ($this->status);
	$this->header ($this->contentType);
	echo $text;
	while (ob_get_level() > 0)
		{
		ob_end_flush();
		}
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
	return empty($this->request[$name]);
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

	$this->redirectToUrl($this->getURL().'?id='.($id == 0 ? $this->Board->getId() : $id).';page='.$class.$param);
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
	$this->header('Location: '.$url);
	exit();
	}

private function curlInit($url)
	{
	if (!preg_match('/^(https?|ftp):\/\//', $url))
		{
 		throw new RuntimeException('Nur http und ftp-Protokolle erlaubt', 0);
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
	$ype = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
	curl_close($curl);

	return array('type' => $ype, 'content' => $content);
	}

public function getUploadedFile($name)
	{
	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name']))
		{
		return $_FILES[$name];
		}
	else
		{
		throw new IoException('Datei wurde nicht hochgeladen! Die Datei ist möglicherweise größer als '.ini_get('upload_max_filesize').'Byte .');
		}
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

?>
