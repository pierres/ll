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
		$this->file = $this->Input->Request->getInt('file');
		}
	catch (RequestException $e)
		{
		$this->showWarning('keine Datei angegeben');
		}

	$this->checkAccess();
	}

protected function checkAccess()
	{
	try
		{
		/** if one of the threads is private return 0 */
		$stm = $this->DB->prepare
			('
			SELECT
				MIN(threads.forumid)
			FROM
				posts,
				post_attachments,
				threads
			WHERE
				post_attachments.attachment_id = ?
				AND post_attachments.postid = posts.id
				AND posts.threadid = threads.id'
			);
		$stm->bindInteger($this->file);
		$forumid = $stm->getColumn();
		$stm->close();

		/** it seems to be a Private Thread */
		if ($forumid == 0)
			{
			$stm = $this->DB->prepare
				('
				SELECT
					post_attachments.attachment_id
				FROM
					posts,
					post_attachments,
					threads,
					thread_user
				WHERE
					post_attachments.attachment_id = ?
					AND post_attachments.postid = posts.id
					AND posts.threadid = threads.id
					AND threads.forumid = 0
					AND thread_user.threadid = threads.id
					AND thread_user.userid = ?'
				);
			$stm->bindInteger($this->file);
			$stm->bindInteger($this->User->getId());
			$this->file = $stm->getColumn();
			$stm->close();
			}
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Output->setStatus(Output::NOT_FOUND);
		$this->showWarning('Datei nicht gefunden');
		}
	}

public function show()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				name,
				type,
				content,
				size
			FROM
				attachments
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->file);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Output->setStatus(Output::NOT_FOUND);
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