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
/**
* @TODO: remove deprecated use of getArray()
*/
public function getArray($filter = null)
	{
	$result = array();

	if ($filter === null)
		{
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
		}
	else
		{
		foreach($this->request as $name => $request)
			{
			if(is_array($request) && $name == $filter)
				{
				foreach($request as $key => $value)
					{
					$result[] = $value;
					}
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

private function curlInit($url)
	{
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 3);
	curl_setopt($curl, CURLOPT_TIMEOUT, 15);
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
