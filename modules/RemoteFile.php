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

class RemoteFile extends File {

private $url 		= '';
private $fileName 	= '';
private $size 		= 0;
private $remoteSize 	= 0;
private $type		= '';
private $content 	= '';
private $fetchedContent	= false;


public function __construct($url)
	{
	if (!preg_match('/^(https?|ftp):\/\//', $url))
		{
 		throw new FileException('Nur http und ftp-Protokolle erlaubt');
		}

	$this->url = $url;
	$this->fileName = preg_replace('/.*\/([^\/]+)/', '$1', $this->url);
	}

public function getFileName()
	{
	return $this->fileName;
	}

public function getFileUrl()
	{
	return $this->url;
	}

public function getFileSize()
	{
	if (!$this->fetchedContent)
		{
		$this->getFileContent();
		}

	return $this->size;
	}

public function getRemoteFileSize()
	{
	if (!$this->fetchedContent)
		{
		$curl = $this->curlInit($this->url);
		curl_setopt($curl, CURLOPT_NOBODY, true);
		curl_exec($curl);
		$this->size = curl_getinfo($curl, CURLINFO_CONTENT_LENGTH_DOWNLOAD);
		curl_close($curl);
		}

	return $this->size;
	}

public function getFileType()
	{
	if (!$this->fetchedContent)
		{
		$this->getFileContent();
		}

	return $this->type;
	}

public function getFileContent()
	{
	if (!$this->fetchedContent)
		{
		$curl = $this->curlInit($this->url);
		$this->content = curl_exec($curl);
		$this->type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
		curl_close($curl);

		try
			{
			$this->type = $this->getTypeFromContent($this->content);
			}
		catch (FileException $e)
			{
// 			we will use the type provides by the client
			}

		if (!$this->isAllowedType($this->type))
			{
			throw new FileException('Dateien des Typs <strong>'.htmlspecialchars($this->type).'</strong> dürfen nicht hochgeladen werden! Folgende Typen sind erlaubt:<ul><li>'.implode('</li><li>', $this->Settings->getValue('allowed_mime_types')).'</li></ul>');
			}

		$this->size = strlen($this->content);
		$this->fetchedContent = true;
		}

	return $this->content;
	}

private function curlInit($url)
	{
	$curl = curl_init($url);
	curl_setopt($curl, CURLOPT_FAILONERROR, true);
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($curl, CURLOPT_MAXREDIRS, 1);
	curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($curl, CURLOPT_ENCODING, '');
	curl_setopt($curl, CURLOPT_USERPWD, 'anonymous:'.$this->Settings->getValue('email'));

	return $curl;
	}

private function getTypeFromContent($content)
	{
	if (function_exists('finfo_open'))
		{
		$finfo = finfo_open(FILEINFO_MIME);
		$type = finfo_buffer($finfo, $content);
		finfo_close($finfo);
		$type = strtok($type, ';');
		}
	else
		{
		throw new FileException('No fileinfo module found');
		}

	return $type;
	}

}


?>
