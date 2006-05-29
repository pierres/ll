<?php

class SplitThread extends Form{

private $post 			= 0;
private $oldthread 		= 0;
private $newthread 	= 0;
private $forum	 	= 0;
private $newtopic 		= '';
protected $title 		= 'Beiträge abzweigen';


protected function setForm()
	{
	$this->setValue('title', $this->title);

	try
		{
		$this->post = $this->Io->getInt('post');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('Welcher Beitrag?');
		}

	$this->checkAccess();

	try
		{
		$this->newtopic = $this->Io->getString('newtopic');
		}
	catch (IoException $e)
		{
		}

	$this->addSubmit('Thema erstellen');

	$this->addText('newtopic', 'Neues Thema', $this->newtopic);
	$this->requires('newtopic');
	$this->setLength('newtopic', 3, 100);

	$this->addHidden('post', $this->post);
	}

protected function checkForm()
	{
	}

protected function checkAccess()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.mods,
				forums.id,
				threads.id AS threadid
			FROM
				posts,
				threads,
				forums
			WHERE
				threads.id = posts.threadid
				AND threads.forumid = forums.id
				AND posts.deleted = 0
				AND threads.deleted = 0
				AND threads.closed = 0
				AND posts.id = ?
				AND threads.firstdate <> posts.dat
			');
		$stm->bindInteger($this->post);
		$forum = $stm->getRow();
		$stm->close();

		$this->forum = $forum['id'];
		$this->oldthread = $forum['threadid'];
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
		}

	if (!$this->User->isMod() && !$this->User->isGroup($forum['mods']))
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		INSERT INTO
			threads
		SET
			name = ?,
			forumid = ?
		');
	$stm->bindString(htmlspecialchars($this->newtopic));
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();

	$this->newthread = $this->DB->getInsertId();

	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			threads = threads + 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			threadid = ?
		WHERE
			threadid = ?
			AND id >= ?'
		);
	$stm->bindInteger($this->newthread);
	$stm->bindInteger($this->oldthread);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	AdminFunctions::updateThread($this->oldthread);
	AdminFunctions::updateThread($this->newthread);
	AdminFunctions::updateForum($this->forum);

	$this->sendThreadSummary();

	$this->redirect();
	}

protected function sendThreadSummary()
	{
	$stm = $this->DB->prepare
		('
		SELECT
			text
		FROM
			posts
		WHERE
			id = ?
		');
	$stm->BindInteger($this->post);
	$text = $stm->GetColumn();
	$stm->close();

	$summary = str_replace('<br />', ' ', $text);
	$summary = str_replace("\n", ' ', strip_tags($summary));
	$summary = cutString($summary,  300);

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			summary = ?
		WHERE
			id = ?
		');

	$stm->bindString($summary);
	$stm->bindInteger($this->newthread);
	$stm->execute();
	$stm->close();
	}

protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>