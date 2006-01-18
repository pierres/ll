<?php


class DeleteUser extends Form{

private $user = 0;

protected function setForm()
	{
	try
		{
		$this->user = $this->Io->getInt('user');
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Benutzer angegeben');
		}

	if (!$this->User->isOnline() && ($this->User->getID() != $this->user || !$this->User->isAdmin()))
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->setValue('title', 'Benutzerkonto löschen');

	$this->addSubmit('Bestätigen');

	try
		{
		$username = $this->Sql->fetchValue
			('
			SELECT
				name
			FROM
				users
			WHERE
				id = '.$this->user
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Kein Benutzer gefunden');
		}

	$this->addElement('hint', 'Soll das Benutzerkonto von <strong>'.$username.'</strong> wirklich gelöscht werden?');

	$this->addRadio('confirm', 'Bestätige Deine Entscheidung',
	array('Ja, ich möchte dieses Benutzerkonto endgültig löschen.' => 1, 'Nein, lieber doch nichts löschen.' => 0), 0);
	$this->setLength('gender', 1, 1);
	}

protected function checkForm()
	{
	if (!$this->User->isOnline() && ($this->User->getID() != $this->user || !$this->User->isAdmin()))
		{
		$this->showWarning('Na, was hattest Du denn vor?');
		}
	}

protected function sendForm()
	{
	$this->Sql->query
		('
		DELETE FROM
			users
		WHERE
			id = '.$this->user
		);

	$this->Sql->query
		('
		DELETE FROM
			poll_voters
		WHERE
			userid = '.$this->user
		);
/*
	$this->Sql->query
		('
		DELETE FROM
			files
		WHERE
			userid = '.$this->user
		);
*/
	$this->Sql->query
		('
		DELETE FROM
			thread_user
		WHERE
			userid = '.$this->user
		);

	$this->Sql->query
		('
		DELETE FROM
			threads_log
		WHERE
			userid = '.$this->user
		);

	$this->Sql->query
		('
		DELETE FROM
			user_group
		WHERE
			userid = '.$this->user
		);

	$this->Sql->query
		('
		UPDATE
			threads
		SET
			firstuserid = 0
		WHERE
			firstuserid = '.$this->user
		);

	$this->Sql->query
		('
		UPDATE
			threads
		SET
			lastuserid = 0
		WHERE
			lastuserid = '.$this->user
		);

	if ($this->user == $this->User->getId())
		{
		$this->User->logout();
		}

	$this->Io->redirect('Forums');
	}

}


?>