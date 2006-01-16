<?php

class GetImage extends Page{

private $data 	= array();
private $url 	= '';
private $thumb	= '';

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
	$size = 300;
	$src = imagecreatefromstring($content);
	$width = imagesx($src);
	$height = imagesy($src);
	$aspect_ratio = $height/$width;

	if ($width <= $size)
		{
		return '';
		}
	else
		{
		$new_w = $size;
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
		default 		: imagepng($img); 		break;
		}

	$thumb = ob_get_contents();
	ob_end_clean();

	imagedestroy($img);

	return $thumb;
	}

private function getFile()
	{
	$host = parse_url($this->url);
	$link = fsockopen ($host['host'], 80, $errno, $errstr, 10);
	$buffer = '';

	if (!$link)
		{
		echo "$errstr ($errno)<br />\n";
		}
	else
		{
		fputs ($link, 'GET '.$this->url." HTTP/1.0\r\n\r\n");
		while (!feof($link))
			{
			$buffer .= fgets($link,2048);
			}
		fclose($link);
		}

	$result = explode("\r\n\r\n", $buffer);
	unset($buffer);

	preg_match('/Content-Type: (.*)/s', $result[0], $type);

	return array('type' => trim($type[1]), 'content' => $result[1]);
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