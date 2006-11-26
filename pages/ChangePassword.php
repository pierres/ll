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
	$this->password = sha1($this->Io->getString('password'));

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				users
			WHERE
				id = ?
				AND password =?'
			);
		$stm->bindInteger($this->User->getId());
		$stm->bindString($this->password);
		$stm->getRow();
		$stm->close();
		}
	catch(DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Passwort ist falsch');
		}

	$this->newpassword = sha1($this->Io->getString('newpassword'));

	if ($this->newpassword != sha1($this->Io->getString('confirm')))
		{
		$this->showWarning('Du hast Dich vertippt!');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			password = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->newpassword);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	if($this->Io->isRequest('cookiepw') && $this->Io->getHex('cookiepw') == $this->password)
		{
		$this->Io->setCookie('cookiepw', sha1($this->Settings->getValue('cookie_hash').$this->newpassword));
		}

	$this->Io->redirect('MyProfile');
	}

}

?>