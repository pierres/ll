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
class GetAttachment extends GetFile{


protected $file = 0;

protected function getParams()
	{
	try
		{
		$this->file = $this->Io->getInt('file');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('keine Datei angegeben');
		}
	}

public function show()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				attachments.name,
				attachments.type,
				attachments.content,
				attachments.size
			FROM
				attachments,
				posts,
				post_attachments,
				threads,
				thread_user
			WHERE
				attachments.id = ?
				AND post_attachments.postid = posts.id
				AND post_attachments.attachment_id = attachments.id
				AND posts.threadid = threads.id
				AND(	(
					threads.forumid = 0
					AND thread_user.threadid = threads.id
					AND thread_user.userid = ?
					)
				OR
					threads.forumid > 0)'
			);
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

	if (strpos($data['type'], 'image/') === 0)
		{
 		$this->sendInlineFile($data['type'], $data['name'], $data['size'], $data['content']);
		}
	else
		{
		$this->sendFile($data['type'], $data['name'], $data['size'], $data['content']);
		}
	}

}

?>