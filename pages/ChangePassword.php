<?php

class ChangePassword extends Form{

private $newpassword	= '';
private $password 	= '';



protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->setValue('title', 'Passwort ändern');

	$this->addSubmit('Ändern');

	$this->addPassword('password', 'Dein Passwort', '', 25);
	$this->requires('password');
	$this->setLength('password', 6, 25);

	$this->addPassword('newpassword', 'Dein neues Passwort', '', 25);
	$this->requires('newpassword');
	$this->setLength('newpassword', 6, 25);

	$this->addPassword('confirm', 'Nocheinmal Dein neues Passwort', '', 25);
	$this->requires('confirm');
	$this->setLength('confirm', 6, 25);
	}

protected function checkForm()
	{
	$this->password = md5($this->Io->getString('password'));

	try
		{
		 $this->Sql->fetchRow
			('
			SELECT
				id
			FROM
				users
			WHERE
				id = '.$this->User->getId().'
				AND password = \''.$this->password.'\''
			);
		}
	catch(SqlNoDataException $e)
		{
		$this->showWarning('Passwort ist falsch');
		}

	$this->newpassword = md5($this->Io->getString('newpassword'));

	if ($this->newpassword != md5($this->Io->getString('confirm')))
		{
		$this->showWarning('Du hast Dich vertippt!');
		}
	}

protected function sendForm()
	{
	$this->Sql->query
		('
		UPDATE
			users
		SET
			password = \''.$this->newpassword.'\'
		WHERE
			id = '.$this->User->getId()
		);

	if($this->Io->isRequest('cookiepw') && $this->Io->getHex('cookiepw') == $this->password)
		{
		$this->Io->setCookie('cookiepw', $this->newpassword);
		}

	$this->Io->redirect('MyProfile');
	}

}

?>