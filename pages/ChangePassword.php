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
	$this->password = sha1($this->Input->Request->getString('password'));

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

	$this->newpassword = sha1($this->Input->Request->getString('newpassword'));

	if ($this->newpassword != sha1($this->Input->Request->getString('confirm')))
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

	if($this->Input->Request->isValid('cookiepw') && $this->Input->Request->getHex('cookiepw') == $this->password)
		{
		$this->Output->setCookie('cookiepw', sha1($this->Settings->getValue('cookie_hash').$this->newpassword));
		}

	$this->Output->redirect('MyProfile');
	}

}

?>