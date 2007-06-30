<?php

class AdminForumsMerge extends AdminForm{

private $source = 0;
private $target = 0;

protected function setForm()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	$this->setValue('title', 'Foren zusammenlegen');
	$this->addSubmit('Zusammenlegen');

	try
		{
		$forums = $this->DB->getRowSet
			('
			SELECT
				id,
				name,
				(SELECT name FROM boards WHERE id = forums.boardid) AS board
			FROM
				forums
			ORDER BY
				board ASC
			');

		$radioArray = array();
		foreach ($forums as $forum)
			{
			$radioArray['<strong>'.$forum['board'].'</strong> '.$forum['name']] = $forum['id'];
			}

		$this->addRadio('source', 'zu verschiebendes Forum', $radioArray);
		$this->requires('source');
		$this->addRadio('target', 'Ziel-Forum', $radioArray);
		$this->requires('target');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function checkForm()
	{
	$this->source = $this->Io->getInt('source');
	$this->target = $this->Io->getInt('target');
	if ($this->source == $this->target)
		{
		$this->showWarning('Quell- und Ziel-Forum sind identisch!');
		}
	}

protected function sendForm()
	{
	set_time_limit(0);
	$this->DB->execute('LOCK TABLES
				posts WRITE,
				threads WRITE,
				forum_cat WRITE,
				forums WRITE
			');

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			forumid = ?
		WHERE
			forumid = ?'
		);
	$stm->bindInteger($this->target);
	$stm->bindInteger($this->source);
	$stm->execute();
	$stm->close();

	AdminFunctions::delForum($this->source);
	$this->DB->execute('UNLOCK TABLES');

	AdminFunctions::updateForum($this->target);

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminGlobalSettings');
	}


}


?>