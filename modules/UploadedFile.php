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

class UploadedFile extends File {

private $file = array();

public function __construct($name)
	{
	if (isset($_FILES[$name]) && is_uploaded_file($_FILES[$name]['tmp_name']))
		{
		$this->file = $_FILES[$name];

		try
			{
			$this->file['type'] = $this->getTypeFromFile($this->file['tmp_name']);
			}
		catch (FileException $e)
			{
			// we will use the type provides by the client
			}

		if (!$this->isAllowedType($this->file['type']))
			{
			throw new FileException('Dateien des Typs <strong>'.htmlspecialchars($this->file['type']).'</strong> dürfen nicht hochgeladen werden! Folgende Typen sind erlaubt:<ul><li>'.implode('</li><li>', $this->Settings->getValue('allowed_mime_types')).'</li></ul>');
			}

		if ($this->getFileSize() >= $this->Settings->getValue('file_size'))
			{
			throw new FileException('Datei ist zu groß!');
			}
		}
	elseif (isset($_FILES[$name]) && !empty($_FILES[$name]['error']))// && !empty($_FILES[$name]['name']))
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

		if ($_FILES[$name]['error'] == 4)
			{
			throw new FileNotUploadedException($message);
			}
		else
			{
			throw new FileException('Datei wurde nicht hochgeladen! '.$message);
			}
		}
	else
		{
		throw new FileNotUploadedException('Datei wurde nicht hochgeladen!');
		}
	}

public function __destruct()
	{
	if (isset($this->file['tmp_name']) && file_exists($this->file['tmp_name']))
		{
		unlink($this->file['tmp_name']);
		}
	}

public function getFileName()
	{
	return $this->file['name'];
	}

public function getFileSize()
	{
	return $this->file['size'];
	}

public function getFileType()
	{
	return $this->file['type'];
	}

public function getFileContent()
	{
	return file_get_contents($this->file['tmp_name']);
	}

private function getTypeFromFile($file)
	{
	if (function_exists('finfo_open'))
		{
		$finfo = finfo_open(FILEINFO_MIME);
		$type = finfo_file($finfo, $file);
		finfo_close($finfo);
		}
	else
		{
		throw new FileException('No fileinfo module found');
		}

	return $type;
	}

}

class FileNotUploadedException extends FileException{}

?>