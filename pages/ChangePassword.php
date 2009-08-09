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

class ChangePassword extends Form {

private $newpassword	= '';
private $password 	= '';



protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder');
		}

	$this->setTitle('Passwort ändern');

	$this->add(new SubmitButtonElement('Ändern'));

	$passwordInput = new PasswordInputElement('password', 'Passwort');
	$passwordInput->setMinLength(6);
	$passwordInput->setMaxLength(25);
	$passwordInput->setSize(25);
	$this->add($passwordInput);

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
	$this->password = sha1($this->Input->Post->getString('password'));

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

	$this->newpassword = sha1($this->Input->Post->getString('newpassword'));

	if ($this->newpassword != sha1($this->Input->Post->getString('confirm')))
		{
		$this->showWarning('Du hast Dich vertippt');
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

	if($this->Input->Cookie->isString('cookiepw') && $this->Input->Cookie->getHex('cookiepw') == $this->password)
		{
		$this->Output->setCookie('cookiepw', sha1($this->Settings->getValue('cookie_hash').$this->newpassword));
		}

	$this->Output->redirect('MyProfile');
	}

}

?>