<?php

/**
* TODO:
*	evtl. timestamp mitführen
*	Konfiguration extrahieren
*/
class GetImage extends Page{

private $data 		= array();
private $url 		= '';
private $thumb		= false;
private $thumbsize 	= 300;
private $maxFileSize 	= 2097152; //2MByte

public function prepare()
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

	if (!$this->User->isOnline())
		{
		if ($this->thumb)
			{
			$this->showWarning($this->url);
			}
		else
			{
			$this->Io->redirectToUrl($this->url);
			}
		}

	try
		{
		if ($this->thumb)
			{
			$this->data = $this->Sql->fetchRow
				('
				(
				SELECT
					type,
					thumbcontent AS content,
					thumbsize AS size
				FROM
					images
				WHERE
					url = \''.$this->Sql->escapeString($this->url).'\'
					AND thumbsize > 0
				)
				UNION
				(
				SELECT
					type,
					content,
					size
				FROM
					images
				WHERE
					url = \''.$this->Sql->escapeString($this->url).'\'
					AND thumbsize = 0
				)
				');
			}
		else
			{
			$this->data = $this->Sql->fetchRow
				('
				SELECT
					type,
					content,
					size
				FROM
					images
				WHERE
					url = \''.$this->Sql->escapeString($this->url).'\'
				');
			}
		}
	catch (SqlNoDataException $e)
		{
		$this->data = $this->loadImage();
		}
	}

private function loadImage()
	{
	try
		{
		if ($this->Io->getRemoteFileSize($this->url) > $this->maxFileSize)
			{
			if ($this->thumb)
				{
				$this->showWarning('Das ist mir zu viel');
				}
			else
				{
				$this->Io->redirectToUrl($this->url);
				}
			}

		$file = $this->Io->getRemoteFile($this->url);
		}
	catch (IoException $e)
		{
		$this->showWarning($e->getMessage());
		}

	$file['size'] = strlen($file['content']);

	$file['thumbcontent'] = resizeImage($file['content'], $file['type'], $this->thumbsize);
	$file['thumbsize'] = strlen($file['thumbcontent']);

	$this->Sql->query
		('
		INSERT INTO
			images
		SET
			url = \''.$this->Sql->escapeString($this->url).'\',
			type = \''.$this->Sql->escapeString($file['type']).'\',
			size = '.$file['size'].',
			content = \''.$this->Sql->escapeString($file['content']).'\',
			thumbcontent = \''.$this->Sql->escapeString($file['thumbcontent']).'\',
			thumbsize = '.$file['thumbsize']
		);

	if ($this->thumb && $file['thumbsize'] > 0)
		{
		return array('type' => $file['type'], 'content' => $file['thumbcontent'], 'size' => $file['thumbsize']);
		}
	else
		{
		return array('type' => $file['type'], 'content' => $file['content'], 'size' => $file['size']);
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
	$content = ob_get_contents();
	ob_end_clean();

	imagedestroy($image);

	$this->data['type'] = 'image/png';
	$this->data['size'] = strlen($content);
	$this->data['content'] = $content;
	$this->url = 'Warning.png';

	$this->show();
	}

public function show()
	{
	$header = 	'Content-Type: '.$this->data['type'].'; name='.$this->url.
			'Content-Disposition: inline; filename="'.$this->url.'"'.
			'Content-length: '.$this->data['size'];

	$this->Io->setContentType($header);
	$this->Io->setOutputHandler('');
	$this->Io->out($this->data['content']);
	}

}

?>