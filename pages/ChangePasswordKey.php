<?php

class ChangePasswordKey extends Form{

private $newpassword	= '';
private $password 	= '';
private $id		= 0;
private $key 		= '';


protected function setForm()
	{
	if ($this->User->isOnline())
		{
		$this->Io->redirect('ChangePassword');
		}

	$this->setValue('title', 'Passwort ändern');
	$this->addSubmit('Ändern');

	$this->addText('userid', 'Benutzer-ID', '', 8);
	$this->requires('userid');
	$this->setLength('userid', 1, 8);

	$this->addText('key', 'Schlüssel', '', 40);
	$this->requires('key');
	$this->setLength('key', 40, 40);

	$this->addPassword('newpassword', 'Dein neues Passwort', '', 25);
	$this->requires('newpassword');
	$this->setLength('newpassword', 6, 25);

	$this->addPassword('confirm', 'Nocheinmal Dein neues Passwort', '', 25);
	$this->requires('confirm');
	$this->setLength('confirm', 6, 25);
	}

protected function checkForm()
	{
	try
		{
		$this->id = $this->Io->getInt('userid');
		$this->key = $this->Io->getHex('key');
		}
	catch (IoRequestException $e)
		{
		$this->showFailure('Kein Schlüssel übergeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				request_time
			FROM
				password_key
			WHERE
				id = ?
				AND `key` = ?
			');
		$stm->bindInteger($this->id);
		$stm->bindString($this->key);
		$requestTime = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Falscher Schlüssel!');
		}

	if (time() - $requestTime >= $this->Settings->getValue('password_key_lifetime'))
		{
		$stm = $this->DB->prepare
			('
			DELETE FROM
				password_key
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$stm->execute();
		$stm->close();
		$this->showFailure('Dein Schlüssel ist abgelaufen! Es ist zuviel Zeit zwischen Registrierung und Aktivierung verstrichen.<br />Lasse Dir bitte <a class="link" href="?page=ForgotPassword;id='.$this->Board->getId().'">erneut einen Schlüssel zusenden</a> und aktiviere Dein Konto umgehend.');
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
		DELETE FROM
			password_key
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();

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
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();

	try
		{
		$this->User->login($this->id, $this->newpassword, true);
		}
	catch (LoginException $e)
		{
		// Ich kann warten...
		sleep(5);
		$this->showFailure('Falsches Passwort.');
		}

	$this->Io->redirect('Forums');
	}

}

?>