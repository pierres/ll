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

private $file 		= null;
private $thumb		= false;


protected function getParams()
	{
	$this->thumb = $this->Input->Get->isString('thumb');

	try
		{
		$url = $this->Input->Get->getString('url');
		}
	catch (RequestException $e)
		{
		$this->showWarning('Keine Datei angegeben');
		}

	if ($this->Input->Get->isEmptyString('url'))
		{
		$this->showWarning('Keine Datei angegeben');
		}

	try
		{
		$this->file = $this->Input->getRemoteFile($url);
		}
	catch (FileException $e)
		{
		$this->showWarning($e->getMessage());
		}
	}

private function loadImage()
	{
	try
		{
		// ask the remote Server first...
		if ($this->file->getRemoteFileSize() > $this->Settings->getValue('max_image_file_size'))
			{
			$this->showWarning('Diese Datei ist zu groß');
			}
		// ...but do not trust him (this fetches the file first)
		if ($this->file->getFileSize() > $this->Settings->getValue('max_image_file_size'))
			{
			$this->showWarning('Diese Datei ist zu groß');
			}

		if (	strpos($this->file->getFileType(), 'image/jpeg') !== 0 &&
			strpos($this->file->getFileType(), 'image/png') !== 0 &&
			strpos($this->file->getFileType(), 'image/gif') !== 0)
			{
			$this->showWarning('Diese URL enthält kein Bild');
			}

		try
			{
			$thumbcontent = resizeImage($this->file->getFileContent(), $this->file->getFileType(), $this->Settings->getValue('thumb_size'));
			}
		catch (Exception $e)
			{
			$thumbcontent = '';
			}
		}
	catch (FileException $e)
		{
		$this->Output->setStatus(Output::NOT_FOUND);
 		$this->showWarning('Das Bild konnte nicht geladen werden');
		}

	$stm = $this->DB->prepare
		('
		REPLACE INTO
			images
		SET
			url = ?,
			type = ?,
			content = ?,
			thumbcontent = ?,
			lastupdate = ?'
		);
	$stm->bindString($this->file->getFileUrl());
	$stm->bindString($this->file->getFileType());
	$stm->bindString($this->file->getFileContent());
	$stm->bindString($thumbcontent);
	$stm->bindInteger($this->Input->getTime());
	$stm->execute();
	$stm->close();

	if ($this->thumb && $thumbsize > 0)
		{
		return array('type' => $this->file->getFileType(), 'content' => $thumbcontent, 'name' => $this->file->getFileName());
		}
	else
		{
		return array('type' => $this->file->getFileType(), 'content' => $this->file->getFileContent(), 'name' => $this->file->getFileName());
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

	$this->sendInlineFile('image/png', 'Warning.png', $content);
	}

public function show()
	{
	try
		{
		if ($this->thumb)
			{
			$stm = $this->DB->prepare
				('
				SELECT
					type,
					thumbcontent AS content,
					lastupdate
				FROM
					images
				WHERE
					url = ?
				');
			$stm->bindString($this->file->getFileUrl());
			$data = $stm->getRow();
			$stm->close();
			}		
		if (!$this->thumb || empty($data['content']))
			{
			$stm = $this->DB->prepare
				('
				SELECT
					type,
					content,
					lastupdate
				FROM
					images
				WHERE
					url = ?
				');
			$stm->bindString($this->file->getFileUrl());
			$data = $stm->getRow();
			$stm->close();
			}

		$refreshTime = $this->Input->getTime() - $this->Settings->getValue('image_refresh');

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

	$this->sendInlineFile($data['type'], $this->file->getFileName(), $data['content']);
	}

}

?>