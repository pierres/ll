<?php

class ChangePasswordKey extends Form{

private $newpassword	= '';
private $password 	= '';
private $id		= 0;


protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		try
			{
			$this->id = $this->Io->getInt('userid');
			$key = $this->Io->getHex('key');

			$stm = $this->DB->prepare
				('
				SELECT
					request_time
				FROM
					change_password
				WHERE
					id = ?
					AND `key` = ?
				');
			$stm->bindInteger($this->id);
			$stm->bindString($key);
			$requestTime = $stm->getColumn();
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			$this->showFailure('Falscher Schlüssel!');
			}
		catch (IoRequestException $e)
			{
			$this->showFailure('Kein Schlüssel übergeben!');
			}

		if (time() - $requestTime >= $this->Settings->getValue('password_key_lifetime'))
			{
			$stm = $this->DB->prepare
				('
				DELETE FROM
					change_password
				WHERE
					id = ?'
				);
			$stm->bindInteger($this->id);
			$stm->execute();
			$stm->close();
			$this->showFailure('Dein Schlüssel ist abgelaufen! Es ist zuviel Zeit zwischen Registrierung und Aktivierung verstrichen.<br />Lasse Dir bitte <a class="link" href="?page=ForgotPassword;id='.$this->board->getId().'">erneut einen Schlüssel zusenden</a> und aktiviere Dein Konto umgehend.');
			}

		$this->addHidden('userid', $this->id);
		$this->addHidden('key', $key);
		}
	else
		{
		$this->Io->redirect('ChangePassword');
		}

	$this->setValue('title', 'Passwort ändern');

	$this->addSubmit('Ändern');

	$this->addPassword('newpassword', 'Dein neues Passwort', '', 25);
	$this->requires('newpassword');
	$this->setLength('newpassword', 6, 25);

	$this->addPassword('confirm', 'Nocheinmal Dein neues Passwort', '', 25);
	$this->requires('confirm');
	$this->setLength('confirm', 6, 25);
	}

protected function checkForm()
	{
	$this->newpassword = md5($this->Io->getString('newpassword'));

	if ($this->newpassword != md5($this->Io->getString('confirm')))
		{
		$this->showWarning('Du hast Dich vertippt!');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			change_password
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