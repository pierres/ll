<?php

class GetAttachmentThumb extends GetAttachment{

public function show()
	{
	try
		{
		/** @TODO: Optimieren! */
		$stm = $this->DB->prepare
			('
			(
			SELECT
				attachments.name,
				attachments.type,
				attachment_thumbnails.content,
				attachment_thumbnails.size
			FROM
				attachments,
				attachment_thumbnails
			WHERE
				attachments.id = ?
				AND attachments.id = attachment_thumbnails.id
			)
			UNION
			(
			SELECT
				name,
				type,
				content,
				size
			FROM
				attachments
			WHERE
				id = ?
				AND id NOT IN (SELECT id FROM attachment_thumbnails)
			)'
			);
		$stm->bindInteger($this->file);
		$stm->bindInteger($this->file);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Datei nicht gefunden');
		}

	$this->sendInlineFile($data['type'], $data['name'], $data['size'], $data['content']);
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

}

?>