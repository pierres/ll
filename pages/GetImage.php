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
class GetImage extends GetFile{

private $url 		= '';
private $name 		= '';
private $thumb		= false;


protected function getParams()
	{
	$this->thumb = $this->Io->isRequest('thumb');

	try
		{
		$this->url = $this->Io->getString('url');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('keine Datei angegeben');
		}

	if (empty($this->url))
		{
		$this->showWarning('keine Datei angegeben');
		}

	$this->name = preg_replace('/.*\/([^\/]+)/', '$1', $this->url);
	}

private function loadImage()
	{
	try
		{
		if ($this->Io->getRemoteFileSize($this->url) > $this->Settings->getValue('max_image_file_size'))
			{
			$this->showWarning('Diese Datei ist zu groß.');
			}

		$file = $this->Io->getRemoteFile($this->url);
		}
	catch (IOMimeExceptionException $e)
		{
 		$this->showWarning('Diese URL enthält kein Bild.');
		}
	catch (Exception $e)
		{
		$this->Io->setStatus(Io::NOT_FOUND);
 		$this->showWarning('Das Bild konnte nicht geladen werden.');
		}

	if (	strpos($file['type'], 'image/jpeg') !== 0 &&
		strpos($file['type'], 'image/png') !== 0 &&
		strpos($file['type'], 'image/gif') !== 0)
		{
 		$this->showWarning('Diese URL enthält kein Bild.');
		}

	$file['size'] = strlen($file['content']);

	try
		{
		$file['thumbcontent'] = resizeImage($file['content'], $file['type'], $this->Settings->getValue('thumb_size'));
		}
	catch (Exception $e)
		{
		$file['thumbcontent'] = '';
		}

	$file['thumbsize'] = strlen($file['thumbcontent']);

	$stm = $this->DB->prepare
		('
		REPLACE INTO
			images
		SET
			url = ?,
			type = ?,
			size = ?,
			content = ?,
			thumbcontent = ?,
			thumbsize = ?,
			lastupdate = ?'
		);
	$stm->bindString($this->url);
	$stm->bindString($file['type']);
	$stm->bindInteger($file['size']);
	$stm->bindString($file['content']);
	$stm->bindString($file['thumbcontent']);
	$stm->bindInteger($file['thumbsize']);
	$stm->bindInteger(time());
	$stm->execute();
	$stm->close();

	if ($this->thumb && $file['thumbsize'] > 0)
		{
		return array('type' => $file['type'], 'content' => $file['thumbcontent'], 'size' => $file['thumbsize'], 'name' => $this->name);
		}
	else
		{
		return array('type' => $file['type'], 'content' => $file['content'], 'size' => $file['size'], 'name' => $this->name);
		}
	}

public function showWarning($text)
	{
	$text = utf8_decode($text);
	$font = -1;
	$width  = imagefontwidth($font) * strlen($text);
	$height = imagefontheight($font);
	$image = imagecreate($width+8, $height+4);
	$white = imagecolorallocate($image, 255, 255, 255);
	$black = imagecolorallocate($image, 0, 0, 0);

	imagecolortransparent($image, $white).

	imagestring($image, $font, 4, 2, $text , $black);

	ob_start();
	imagepng($image);
	$content = ob_get_clean();

	imagedestroy($image);

	$data['type'] = 'image/png';
	$data['size'] = strlen($content);
	$data['content'] = $content;
	$this->url = 'Warning.png';

	$this->sendInlineFile($data['type'], 'Warning.png', $data['size'], $data['content']);
	}

public function show()
	{
	try
		{
		if ($this->thumb)
			{
			$stm = $this->DB->prepare
				('
				(
				SELECT
					type,
					thumbcontent AS content,
					thumbsize AS size,
					lastupdate
				FROM
					images
				WHERE
					url = ?
					AND thumbsize > 0
				)
				UNION
				(
				SELECT
					type,
					content,
					size,
					lastupdate
				FROM
					images
				WHERE
					url = ?
					AND thumbsize = 0
				)
				');
			$stm->bindString($this->url);
			$stm->bindString($this->url);
			$data = $stm->getRow();
			$stm->close();
			}
		else
			{
			$stm = $this->DB->prepare
				('
				SELECT
					type,
					content,
					size,
					lastupdate
				FROM
					images
				WHERE
					url = ?
				');
			$stm->bindString($this->url);
			$data = $stm->getRow();
			$stm->close();
			}

		$refreshTime = time() - $this->Settings->getValue('image_refresh');

		if ($data['lastupdate'] < $refreshTime)
			{
			$stm = $this->DB->prepare
				('
				DELETE FROM
					images
				WHERE
					lastupdate < ?
				');

			$stm->bindInteger($refreshTime);
			$stm->execute();
			$stm->close();
			}
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$data = $this->loadImage();
		}

	$this->sendInlineFile($data['type'], $this->name, $data['size'], $data['content']);
	}

}

?>