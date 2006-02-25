<?php

class DeletedThreads extends Form{

protected function setForm()
	{
	$this->setValue('title', 'Gelöschte Themen');
	$this->addSubmit('Löschen');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showWarning('kein Zugriff!');
		}

	try
		{
		$threads = $this->Sql->fetch
			('
			SELECT
				id,
				name
			FROM
				threads
			WHERE
				deleted = 1
			ORDER BY
				lastdate DESC
			');
		}
	catch (SqlNoDataException $e)
		{
		$data = array();
		}

	foreach ($threads as $thread)
		{
		$this->addOutput('<input type="checkbox" id="id'.$thread['id'].'" name="thread[]" value="'.$thread['id'].'" /><label for="id'.$thread['id'].'"><a href="?page=Postings;id='.$this->Board->getId().';thread='.$thread['id'].'">'.$thread['name'].'</a></label><br />');
		}
	}

protected function sendForm()
	{
	foreach($this->Io->getArray('thread') as $thread)
		{
		AdminFunctions::delThread($thread);
		}
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('DeletedThreads');
	}

}

?>