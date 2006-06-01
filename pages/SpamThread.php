<?php

class SpamThread extends SpamPost{


protected function setForm()
	{
	try
		{
		$this->thread = $this->Io->getInt('thread');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben');
		}

	if (!$this->User->isLevel(User::MOD))
		{
		$this->showFailure('Zutritt nur für Moderatoren!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				posts.text,
				posts.id,
				threads.forumid
			FROM
				posts JOIN threads ON threads.id = posts.threadid AND threads.firstdate = posts.dat
			WHERE
				threads.id = ?
			');
		$stm->bindInteger($this->thread);
		$data = $stm->getRow();
		$text = $data['text'];
		$this->post = $data['id'];
		$this->forum = $data['forumid'];
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Kein Thema gefunden');
		}

	$this->showDomainList($text);
	$this->addHidden('thread', $this->thread);
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			deleted = 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	AdminFunctions::updateForum($this->forum);

	$this->sendDomainList();

	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>