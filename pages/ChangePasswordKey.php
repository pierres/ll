<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
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

	$this->addText('userid', 'Benutzer-ID', '', 25);
	$this->requires('userid');
	$this->setLength('userid', 1, 8);

	$this->addText('key', 'Schlüssel', '', 25);
	$this->requires('key');
	$this->setLength('key', 8, 40);

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
		$this->key = $this->Io->getString('key');
		}
	catch (IoRequestException $e)
		{
		$this->showFailure('Kein Schlüssel übergeben!');
		}

	$this->collectGarbage();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				password_key
			WHERE
				id = ?
				AND `key` = ?
			');
		$stm->bindInteger($this->id);
		$stm->bindString($this->key);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Falscher Schlüssel! Möglicherweise ist Dein Schlüssel abgelaufen, da zuviel Zeit zwischen Registrierung und Aktivierung verstrichen ist.<br />Lasse Dir bitte <a class="link" href="?page=ForgotPassword;id='.$this->Board->getId().'">erneut einen Schlüssel zusenden</a> und aktiviere Dein Konto umgehend.');
		}

	$this->newpassword = sha1($this->Io->getString('newpassword'));

	if ($this->newpassword != sha1($this->Io->getString('confirm')))
		{
		$this->showWarning('Du hast Dich vertippt!');
		}
	}
	
private function collectGarbage()
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			password_key
		WHERE
			request_time < ?'
		);
	$stm->bindInteger(time() - $this->Settings->getValue('password_key_lifetime'));
	$stm->execute();
	$stm->close();
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
		$this->User->login($this->id, sha1($this->Settings->getValue('cookie_hash').$this->newpassword), true);
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