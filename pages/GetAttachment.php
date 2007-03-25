<?php

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