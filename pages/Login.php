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
class Login extends Form{


protected function setForm()
	{
	$this->setValue('title', 'Login');

	$this->addSubmit('Einloggen');

	$this->addText('name', 'Dein Name', !$this->Input->Request->isEmpty('name') ? $this->Input->Request->getHtml('name') : '', 25);
	$this->requires('name');
	$this->setLength('name', 3, 25);

	$this->addPassword('password', 'Dein Passwort', '', 25);
	$this->requires('password');
	$this->setLength('password', 6, 25);

	$this->addCheckbox('cookie', 'Keks benutzen');

// 	$this->addCheckBox('confirmPrivacy', 'Ich bestätige die <a class="link" href="?page=Privacy;id='.$this->Board->getId().'">Datenschutzerklärung</a>');
// 	$this->requires('confirmPrivacy');

	$this->addElement('passwordoptions', '<br /><br /><a href="?page=ForgotPassword;id='.$this->Board->getId().'"><span class="button">Passwort vergessen?</span></a> <a href="?page=ChangePasswordKey;id='.$this->Board->getId().'"><span class="button">Passwort setzen</span></a>');

	if(!$this->Input->Server->isValid('HTTPS'))
		{
		$tls = '<br /><a href="https://'.$this->Input->Server->getString('HTTP_HOST').'/?page=Login;id='.$this->Board->getId().'"><span class="button">TLS-Verschlüsselung</span></a> ';
		}
	else
		{
		$tls = '';
		}

	$this->addElement('tls', $tls.'<br />');
	}

protected function checkForm()
	{
	$name = $this->Input->Request->getHtml('name');
	$password = $this->Input->Request->getString('password');

	try
		{
		$this->User->login($name, $password);
		}
	catch (LoginException $e)
		{
		// Ich kann warten...
		/** TODO: Man muß dann aber auch den jeweiligen Benutzer temporär sperren */
		sleep(5);
		$this->showWarning('Falsches Passwort.');
		}
	}

protected function sendForm()
	{
	if ($this->Input->Request->isValid('cookie'))
		{
		/** @Todo: Das gehört eher nach User **/
		$this->Output->setCookie('cookieid', $this->User->getId(), (time() + $this->Settings->getValue('max_age')));
		$this->Output->setCookie('cookiepw', sha1($this->Settings->getValue('cookie_hash').sha1($this->Input->Request->getString('password'))), (time() + $this->Settings->getValue('max_age')));
		}

	$this->Output->redirect('Forums');
	}

}

?>