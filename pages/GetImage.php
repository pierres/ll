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
		/** FIXME: Hier XSS möglich */
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
/**
		 FIXME: entsprechende BLOB-Befehle von mysqli verwenden
		 TODO: evtl. im Dateisystem zwischenspeichern
*/
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
					thumbsize AS size
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
					size
				FROM
					images
				WHERE
					url = ?
					AND thumbsize = 0
				)
				');
			$stm->bindString($this->url);
			$stm->bindString($this->url);
			$this->data = $stm->getRow();
			}
		else
			{
			$stm = $this->DB->prepare
				('
				SELECT
					type,
					content,
					size
				FROM
					images
				WHERE
					url = ?
				');
			$stm->bindString($this->url);
			$this->data = $stm->getRow();
			}
		}
	catch (DBNoDataException $e)
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
	catch (Exception $e)
		{
		$this->showWarning($e->getMessage());
		}

	$file['size'] = strlen($file['content']);

	$file['thumbcontent'] = resizeImage($file['content'], $file['type'], $this->thumbsize);
	$file['thumbsize'] = strlen($file['thumbcontent']);

	$stm = $this->DB->prepare
		('
		INSERT INTO
			images
		SET
			url = ?,
			type = ?,
			size = ?,
			content = ?,
			thumbcontent = ?,
			thumbsize = ?'
		);
	$stm->bindString($this->url);
	$stm->bindString($file['type']);
	$stm->bindInteger($file['size']);
	$stm->bindString($file['content']);
	$stm->bindString($file['thumbcontent']);
	$stm->bindInteger($file['thumbsize']);
	$stm->execute();

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
	$content = ob_get_clean();

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