<?php

class MovePosting extends Form{

private $moveto 	= 0;
private $post = 0;
private $forum = 0;
private $oldthread 	= 0;


protected function setForm()
	{
	$this->setValue('title', 'Beitrag verschieben');

	try
		{
		$this->post = $this->Io->getInt('post');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('Welcher Beitrag?');
		}

	$this->addHidden('post', $this->post);

	$this->checkAccess();

	$this->buildList();
	}

protected function checkForm()
	{
	try
		{
		$this->moveto = $this->Io->getInt('moveto');
 		$this->checkAccessMoveto();
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('Wohin damit?');
		}
	}

protected function checkAccessMoveto()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.mods
			FROM
				threads,
				forums
			WHERE
				threads.forumid = forums.id
				AND threads.deleted = 0
				AND threads.closed = 0
				AND threads.id = ?
			');
		$stm->bindInteger($this->moveto);
		$mods = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
		}

	if (!$this->User->isMod() && !$this->User->isGroup($mods))
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}
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

protected function buildList()
	{
	$this->addSubmit('Verschieben');

	/** FIXME: Kann man Themen aus dem Board herausschieben ? */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				threads
			WHERE
				id <> ?
				AND forumid = (SELECT forumid FROM threads WHERE id = ?)
			ORDER BY
				lastdate DESC
			LIMIT 50
			');
		$stm->bindInteger($this->oldthread);
		$stm->bindInteger($this->oldthread);

		foreach ($stm->getRowSet() as $data)
			{
			$this->addElement('thread'.$data['id'],
				'<input class="radio" type="radio" name="moveto" value="'.$data['id'].'" />&nbsp;'.$data['name']);
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			threadid = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->moveto);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	AdminFunctions::updateThread($this->oldthread);
	AdminFunctions::updateThread($this->moveto);
	AdminFunctions::updateForum($this->forum);

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>