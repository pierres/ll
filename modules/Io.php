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

	if (get_magic_quotes_gpc())
		{
		die('"magic_quotes_gpc" ist aktiviert!');
		}

// 	if (strpos($this->getEnv('HTTP_ACCEPT'), 'application/xhtml+xml') !== false)
// 		{
// 		$this->contentType = 'Content-Type: application/xhtml+xml; charset=UTF-8';
// 		}
	}

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

public function setCookie($key, $value, $maxage = null)
	{
	setcookie ($key, $value, $maxage);//, '/', '.'.$this->Settings->getValue('domain'));
	}

public function out(&$text)
	{
	ob_start($this->outputHandler);
	$this->header ($this->status);
	$this->header ($this->contentType);
	echo $text;
	ob_end_flush();
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
/**
* FIXME: Prüfe Seiteneffekt bei leerem Rückgabewert
*/
public function getString($name)
	{
	if (isset($this->request[$name]))
		{
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

public function getArray()
	{
	$result = array();

	foreach($this->request as $name => $request)
		{
		if(is_array($request))
			{
			foreach($request as $key => $value)
				{
				$result[$key][$name] = $value;
				}
			}
		}

	return $result;
	}

public function getLength($name)
	{
	return (empty($this->request[$name]) ? 0 : strlen($this->getString($name)));
	}

public function redirect($class, $param = '', $id = 0)
	{
	$param = (!empty($param) ? ';'.$param : '');

	$this->redirectToUrl('http'.(!getenv('HTTPS') ? '' : 's').'://'.
				getenv('HTTP_HOST')
				.dirname($_SERVER['PHP_SELF'])
				.'?id='.($id == 0 ? $this->Board->getId() : $id).';page='.$class.$param);
	}

public function redirectToUrl($url)
	{
	$this->header('Location: '.$url);
	exit();
	}

private function parseURL($url)
	{
	$request = @parse_url($url);

	if (!$request)
		{
		throw new IoException('Konnte die URL '.$url.' nicht auflösen.');
		}

	return $request;
	}

private function openRemoteFile($host)
	{
	$link = @fsockopen($host, 80, $errno, $errstr, 10);


	if (!$link)
		{
		throw new IoException('Konnte keine Verbindung zu '.$host.' herstellen: '.$errstr);
		}

	return $link;
	}

public function getRemoteFileSize($url)
	{
	$request = $this->parseURL($url);
	$link = $this->openRemoteFile($request['host']);

	fputs ($link, 'HEAD '.$request['path']." HTTP/1.0\r\nHost: ".$request['host']."\r\n\r\n");

	$buffer = '';
	while (!feof($link))
		{
		$buffer .= fgets($link, 512);
		}
	fclose($link);

	if (!preg_match('/Content-Length: (.*)/', $buffer, $size))
		{
		throw new IoException('Konnte die Datei nicht laden.');
		}

	return $size[1];
	}

public function getRemoteFile($url)
	{
	$request = $this->parseURL($url);
	$link = $this->openRemoteFile($request['host']);

	$buffer = '';

	fputs ($link, 'GET '.$request['path']." HTTP/1.0\r\nHost: ".$request['host']."\r\n\r\n");
	while (!feof($link))
		{
		$buffer .= fgets($link, 8192);
		}
	fclose($link);

	$result = explode("\r\n\r\n", $buffer);/** FIXME */
	unset($buffer);

	if (!preg_match('/Content-Type: (.*)/', $result[0], $type))
		{
		throw new IoException('Konnte die Datei nicht laden.');
		}

	return array('type' => trim($type[1]), 'content' => $result[1]);
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
