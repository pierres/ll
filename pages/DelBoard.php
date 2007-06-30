<?php

class DelBoard extends AdminForm{

protected function setForm()
	{
	$this->setValue('title', 'Board löschen');
	$this->addSubmit('Löschen');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				boards
			WHERE
				id <> ?
			ORDER BY
				name ASC
			');
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $board)
			{
			$radioArray[$board['name']] = $board['id'];
			}
		$stm->close();
		
		$this->addRadio('board', 'Welches Board soll gelöscht werden?', $radioArray);
		$this->requires('board');
		$this->addCheckBox('sure', 'Mir ist klar, das dadurch alle Daten verloren gehen.');
		$this->requires('sure');
		}
	catch (DBNoDataException $e)
		{
		}
	}

protected function sendForm()
	{
	set_time_limit(0);
	$this->DB->execute('LOCK TABLES
				attachments WRITE,
				attachment_thumbnails WRITE,
				boards WRITE,
				cats WRITE,
				forum_cat WRITE,
				forums WRITE,
				poll_values WRITE,
				poll_voters WRITE,
				polls WRITE,
				post_attachments WRITE,
				posts WRITE,
				thread_user WRITE,
				threads WRITE,
				threads_log WRITE,
				user_group WRITE
			');
	AdminFunctions::delBoard($this->Io->getInt('board'));
	$this->DB->execute('UNLOCK TABLES');

	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('DelBoard');
	}

}

?>