<?php

class SplitThread extends Form{

private $post 		= 0;
private $oldthread 	= 0;
private $forum	 	= 0;
private $newtopic 	= '';
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

		$this->forum = $forum['id'];
		$this->oldthread = $forum['threadid'];
		}
	catch (DBNoDataException $e)
		{
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

	$newThread = $this->DB->getInsertId();

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
	$stm->bindInteger($newThread);
	$stm->bindInteger($this->oldthread);
	$stm->bindInteger($this->post);
	$stm->execute();

	AdminFunctions::updateThread($this->oldthread);
	AdminFunctions::updateThread($newThread);
	AdminFunctions::updateForum($this->forum);

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>