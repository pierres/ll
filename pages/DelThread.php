<?php

class DelThread extends Form{

protected $forum 		= 0;
protected $thread		= 0;
private $deleted 		= false;

protected function setForm()
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
				forumid,
				deleted
			FROM
				threads
			WHERE
				closed = 0
				AND id = ?
			');
		$stm->bindInteger($this->thread);
		$result = $stm->getRow();
		$this->forum = $result['forumid'];
		$this->deleted = $result['deleted'];
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
		}

	$this->setValue('title', 'Thema '.($this->deleted ? 'wiederherstellen' : 'löschen'));

	$this->addHidden('thread', $this->thread);
	$this->requires('thread');

	$this->addOutput('Soll das Thema wirklich '.($this->deleted ? 'wiederhergestellt' : 'gelöscht').' werden?');

	$this->addSubmit('Thema '.($this->deleted ? 'wiederherstellen' : 'löschen'));
	}

protected function checkForm()
	{
	if (!$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Thema gefunden.');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			deleted = ABS(deleted - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->updateForum();

	$this->redirect();
	}

protected function updateForum()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateForum($this->forum);
	}

protected function redirect()
	{
	$this->Io->redirect('Postings', 'thread='.$this->thread);
	}

}

?>