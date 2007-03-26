<?php

class DelPrivateThread extends Form{

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
		$this->showFailure('Kein Privates Thema angegeben!');
		}

	if (!$this->User->isOnline())
		{
		$this->showWarning('Nur für Mitglieder');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				deleted
			FROM
				threads
			WHERE
				id = ?
				AND firstuserid = ?
			');
		$stm->bindInteger($this->thread);
		$stm->bindInteger($this->User->getId());
		$result = $stm->getRow();
		$this->deleted = $result['deleted'];
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Privates Thema nicht gefunden!');
		}

	$this->setValue('title', 'Privates Thema '.($this->deleted ? 'wiederherstellen' : 'löschen'));

	$this->addHidden('thread', $this->thread);
	$this->requires('thread');

	$this->addOutput('Soll das Private Thema wirklich '.($this->deleted ? 'wiederhergestellt' : 'gelöscht').' werden?');

	$this->addSubmit('Privates Thema '.($this->deleted ? 'wiederherstellen' : 'löschen'));
	}

protected function checkForm()
	{
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

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('PrivatePostings', 'thread='.$this->thread);
	}

}

?>