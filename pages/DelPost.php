<?php

class DelPost extends Form{

private $post = 0;
private $thread = 0;
private $forum = 0;
private $deleted = false;

protected function setForm()
	{
	try
		{
		$this->post = $this->Io->getInt('post');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Beitrag angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.forumid,
				threads.id,
				posts.deleted
			FROM
				threads JOIN posts ON posts.threadid = threads.id
			WHERE
				threads.deleted = 0
				AND threads.closed = 0
				AND posts.id = ?
				AND threads.forumid <> 0
			');
		$stm->bindInteger($this->post);
		$data = $stm->getRow();
		$stm->close();

		$this->thread = $data['id'];
		$this->forum = $data['forumid'];
		$this->deleted = $data['deleted'];
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Beitrag nicht gefunden oder Thema geschlossen!');
		}

	$this->setValue('title', 'Beitrag '.($this->deleted ? 'wiederherstellen' : 'löschen'));

	$this->addHidden('post', $this->post);
	$this->requires('post');

	$this->addOutput('Soll der Beitrag wirklich '.($this->deleted ? 'wiederhergetstellt' : 'gelöscht').' werden?');

	$this->addSubmit('Beitrag '.($this->deleted ? 'wiederherstellen' : 'löschen'));
	}

protected function checkForm()
	{
	/** TODO: evtl. auch eigene Posts löschen */
	if (!$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			deleted = ABS(deleted - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	$this->updateThread();
	$this->updateForum();

	$this->redirect();
	}

protected function updateThread()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateThread($this->thread);
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