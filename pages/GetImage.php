<?php

/**
* TODO: Größenbegrenzung -> dann redirect zu URL
*	evtl. timestamp mitführen
*	Konfiguration extrahieren
*/
class GetImage extends Page{

private $data 		= array();
private $url 		= '';
private $thumb		= '';
private $thumbsize 	= 300;
private $maxFileSize 	= 2097152; //2MByte

public function prepare()
	{
	if (!$this->User->isOnline())
		{
		$this->showWarning('Nur für Mitglieder!');
		}

	try
		{
		$this->url = $this->Io->getString('url');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('keine Datei angegeben');
		}

	$this->thumb = $this->Io->isRequest('thumb');

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
					content AS content,
					size AS size
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
	$file = $this->getFile();
	$file['size'] = strlen($file['content']);

	$file['thumbcontent'] = $this->getThumb($file['content'], $file['type']);
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

private function getThumb($content, $type)
	{
	$src = imagecreatefromstring($content);
	$width = imagesx($src);
	$height = imagesy($src);
	$aspect_ratio = $height/$width;

	if ($width <= $this->thumbsize)
		{
		return '';
		}
	else
		{
		$new_w = $this->thumbsize;
		$new_h = abs($new_w * $aspect_ratio);
		}

	$img = imagecreatetruecolor($new_w,$new_h);

	if     ($type == 'image/png')
		{
		imagealphablending($img, false);
		imagesavealpha($img, true);
		}
	elseif ($type == 'image/gif')
		{
		imagealphablending($img, true);
		}

	imagecopyresampled($img,$src,0,0,0,0,$new_w,$new_h,$width,$height);

	ob_start();

	switch ($type)
		{
		case 'image/jpeg' 	: imagejpeg($img, '', 80); 	break;
		case 'image/png' 	: imagepng($img); 		break;
		case 'image/gif' 	: imagegif($img); 		break;
		}

	$thumb = ob_get_contents();
	ob_end_clean();

	imagedestroy($img);

	return $thumb;
	}

private function getFileSize()
	{
	$request = parse_url($this->url);
	$link = fsockopen ($request['host'], 80, $errno, $errstr, 10);
	$buffer = '';

	if (!$link)
		{
		$this->showWarning('Konnte das Bild nicht laden: '.$errstr);
		}
	else
		{
		fputs ($link, 'HEAD '.$request['path']." HTTP/1.0\r\nHost: ".$request['host']."\r\n\r\n");
		while (!feof($link))
			{
			$buffer .= fgets($link, 256);
			}
		fclose($link);
		}

	if (!preg_match('#Content-Type: (image/.*)#', $buffer))
		{
		$this->showWarning('Das ist kein Bild!');
		}

	preg_match('/Content-Length: (.*)/', $buffer, $size);

	return $size[1];
	}

private function getFile()
	{
	if ($this->getFileSize() > $this->maxFileSize)
		{
		if ($this->thumb)
			{
			$this->showWarning('Das ist mir zu viel');
			}
		else
			{
			header('Location: '.$this->url);
			exit;
			}
		}

	$request = parse_url($this->url);

	$link = fsockopen ($request['host'], 80, $errno, $errstr, 10);
	$buffer = '';

	if (!$link)
		{
		$this->showWarning('Konnte das Bild nicht laden: '.$errstr);
		}
	else
		{
		fputs ($link, 'GET '.$request['path']." HTTP/1.0\r\nHost: ".$request['host']."\r\n\r\n");
		while (!feof($link))
			{
			$buffer .= fgets($link,8192);
			}
		fclose($link);
		}

	$result = explode("\r\n\r\n", $buffer);
	unset($buffer);

	preg_match('/Content-Type: (.*)/', $result[0], $type);

	return array('type' => trim($type[1]), 'content' => $result[1]);
	}

public function showWarning($text)
	{
	$font = 2;
	$width  = imagefontwidth($font) * strlen($text);
	$height = imagefontheight($font);
	$image = imagecreate($width+2, $height+2);

	$white = imagecolorallocate($image, 255, 255, 255);
	$black = imagecolorallocate($image, 0, 0, 0);

	imagestring($image, 2, 1, 1,  $text, $black);

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
	header('Content-Type: '.$this->data['type'].'; name='.$this->url);
	header('Content-Disposition: inline; filename="'.$this->url.'"');
	header('Content-length: '.$this->data['size']);

	echo $this->data['content'];
	exit();
	}

}

?>