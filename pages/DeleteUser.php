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

	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	if ($this->User->getID() != $this->user && !$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('Keine Berechtigung!');
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

	$this->addHidden('user', $this->user);

	$this->addElement('hint', 'Soll das Benutzerkonto von <strong><a href="?page=ShowUser;id='.$this->Board->getId().';user='.$this->user.'">'.$username.'</a></strong> wirklich gelöscht werden? <br />Alle Beiträge und angehängten Dateien bleiben erhalten.');

	$this->addRadio('confirm', 'Bestätige Deine Entscheidung',
	array('Ja, ich möchte dieses Benutzerkonto endgültig löschen.' => 1, 'Nein, lieber doch nichts löschen.' => 2), 2);
	$this->requires('confirm');
	$this->setLength('confirm', 1, 1);
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	if ($this->Io->getInt('confirm') == 1)
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
		}

	$this->Io->redirect('Forums');
	}

}


?>