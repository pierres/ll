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
require('GetAttachment.php');

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
					attachment_thumbnails,
					posts,
					post_attachments,
					threads,
					thread_user
				WHERE
					attachments.id = ?
					AND attachments.id = attachment_thumbnails.id
					AND post_attachments.postid = posts.id
					AND post_attachments.attachment_id = attachments.id
					AND posts.threadid = threads.id
					AND(	(
						threads.forumid = 0
						AND thread_user.threadid = threads.id
						AND thread_user.userid = ?
						)
					OR
						threads.forumid > 0)
			)
			UNION
			(
				SELECT
					attachments.name,
					attachments.type,
					attachments.content,
					attachments.size
				FROM
					attachments,
					attachment_thumbnails,
					posts,
					post_attachments,
					threads,
					thread_user
				WHERE
					attachments.id = ?
					AND attachments.id NOT IN (SELECT id FROM attachment_thumbnails)
					AND post_attachments.postid = posts.id
					AND post_attachments.attachment_id = attachments.id
					AND posts.threadid = threads.id
					AND(	(
						threads.forumid = 0
						AND thread_user.threadid = threads.id
						AND thread_user.userid = ?
						)
					OR
						threads.forumid > 0)
			)'
			);
		$stm->bindInteger($this->file);
		$stm->bindInteger($this->User->getId());
		$stm->bindInteger($this->file);
		$stm->bindInteger($this->User->getId());
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
 		$stm->close();
		$this->Io->setStatus(Io::NOT_FOUND);
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

	$this->sendInlineFile($data['type'], 'Warning.png', $data['size'], $data['content']);
	}

}

?>