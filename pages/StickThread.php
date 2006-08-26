<?php

class StickThread extends Page{

protected $forum 		= 0;
protected $thread		= 0;

public function prepare()
	{
	$this->checkInput();
	$this->checkAccess();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			sticky = ABS(sticky - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->updateForum();
	}

protected function checkInput()
	{
	try
		{
		$this->thread = $this->Io->getInt('thread');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forumid
			FROM
				threads
			WHERE
				deleted = 0
				AND id = ?
			');
		$stm->bindInteger($this->thread);
		$this->forum = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden!');
		}
	}

protected function checkAccess()
	{
	if (!$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Thema gefunden.');
		}
	}

protected function updateForum()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateForum($this->forum);
	}

public function show()
	{
	$this->Io->redirect('Postings', 'thread='.$this->thread);
	}

}

?>