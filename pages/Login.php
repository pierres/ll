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

class Login extends Form {


protected function setForm()
	{
	$this->setTitle('Login');

	$this->add(new SubmitButtonElement('Einloggen'));

	$nameInput = new TextInputElement('name', '', 'Dein Name');
	$nameInput->setMinLength(3);
	$nameInput->setMaxLength(25);
	$nameInput->setSize(30);
	$nameInput->setFocus();
	$this->add($nameInput);

	$passwordInput = new PasswordInputElement('password', 'Dein Passwort');
	$passwordInput->setMinLength(6);
	$passwordInput->setMaxLength(25);
	$passwordInput->setSize(30);
	$passwordInput->setHelp('<a href="'.$this->Output->createUrl('ForgotPassword').'">Passwort vergessen?</a>');
	$this->add($passwordInput);

	$cookieInput = new CheckboxInputElement('cookie', 'Keks benutzen');
	$cookieInput->setRequired(false);
	$this->add($cookieInput);
	}

protected function checkForm()
	{
	try
		{
		$this->User->login($this->Input->Post->getHtml('name'), $this->Input->Post->getString('password'));
		}
	catch (LoginException $e)
		{
		$this->showWarning('Falsches Passwort.');
		}
	}

protected function sendForm()
	{
	if ($this->Input->Post->isString('cookie'))
		{
		/** @Todo: Das gehört eher nach User **/
		$this->Output->setCookie('cookieid', $this->User->getId(), ($this->Input->getTime() + $this->Settings->getValue('max_age')));
		$this->Output->setCookie('cookiepw', sha1($this->Settings->getValue('cookie_hash').sha1($this->Input->Post->getString('password'))), ($this->Input->getTime() + $this->Settings->getValue('max_age')));
		}

	$this->Output->redirect('Forums');
	}

}

?>