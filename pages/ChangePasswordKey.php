<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class ChangePasswordKey extends Form {

private $newpassword	= '';
private $password 	= '';
private $id		= 0;
private $key 		= '';


protected function setForm()
	{
	$this->id = $this->Input->Post->getInt('userid', '');
	$this->key = $this->Input->Post->getString('key', '');

	$this->setTitle('Passwort ändern');
	$this->add(new SubmitButtonElement('Ändern'));

	$useridInput = new TextInputElement('userid', $this->id, 'Benutzer-ID');
	$useridInput->setMinLength(1);
	$useridInput->setMaxLength(8);
	$useridInput->setSize(25);
	$this->add($useridInput);

	$keyInput = new TextInputElement('key', $this->key, 'Schlüssel');
	$keyInput->setMinLength(8);
	$keyInput->setMaxLength(40);
	$keyInput->setSize(25);
	$this->add($keyInput);

	$newpasswordInput = new PasswordInputElement('newpassword', 'Neues Passwort');
	$newpasswordInput->setMinLength(6);
	$newpasswordInput->setMaxLength(25);
	$newpasswordInput->setSize(25);
	$this->add($newpasswordInput);

	$confirmInput = new PasswordInputElement('confirm', 'Passwort bestätigen');
	$confirmInput->setMinLength(6);
	$confirmInput->setMaxLength(25);
	$confirmInput->setSize(25);
	$this->add($confirmInput);
	}

protected function checkForm()
	{
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
		$this->showWarning('Falscher Schlüssel! Möglicherweise ist Dein Schlüssel abgelaufen, da zuviel Zeit zwischen Registrierung und Aktivierung verstrichen ist.<br />Lasse Dir bitte <a class="link" href="'.$this->Output->createUrl('ForgotPassword').'">erneut einen Schlüssel zusenden</a> und aktiviere Dein Konto umgehend.');
		}

	$this->newpassword = sha1($this->Input->Post->getString('newpassword'));

	if ($this->newpassword != sha1($this->Input->Post->getString('confirm')))
		{
		$this->showWarning('Du hast Dich vertippt');
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
	$stm->bindInteger($this->Input->getTime() - $this->Settings->getValue('password_key_lifetime'));
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
		$this->showFailure('Falsches Passwort');
		}

	$this->Output->redirect('Forums');
	}

}

?>