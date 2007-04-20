<?php


class Login extends Form{


protected function setForm()
	{
	$this->setValue('title', 'Login');

	$this->addSubmit('Einloggen');

	$this->addText('name', 'Dein Name', !$this->Io->isEmpty('name') ? $this->Io->getHtml('name') : '', 25);
	$this->requires('name');
	$this->setLength('name', 3, 25);

	$this->addPassword('password', 'Dein Passwort', '', 25);
	$this->requires('password');
	$this->setLength('password', 6, 25);

	$this->addCheckbox('cookie', 'Keks benutzen');

// 	$this->addCheckBox('confirmPrivacy', 'Ich bestätige die <a class="link" href="?page=Privacy;id='.$this->Board->getId().'">Datenschutzerklärung</a>');
// 	$this->requires('confirmPrivacy');

	$this->addElement('passwordoptions', '<br /><br /><a href="?page=ForgotPassword;id='.$this->Board->getId().'"><span class="button">Passwort vergessen?</span></a> <a href="?page=ChangePasswordKey;id='.$this->Board->getId().'"><span class="button">Passwort setzen</span></a>');

	if(!$this->Io->getEnv('HTTPS'))
		{
		$tls = '<br /><a href="https://'.$this->Io->getEnv('HTTP_HOST').'/?page=Login;id='.$this->Board->getId().'"><span class="button">TLS-Verschlüsselung</span></a> ';
		}
	else
		{
		$tls = $this->Settings->getValue('tls_enabled_message');
		}

	$this->addElement('tls', $tls.'<br />');
	}

protected function checkForm()
	{
	$name = $this->Io->getHtml('name');
	$password = $this->Io->getString('password');

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
	if ($this->Io->isRequest('cookie'))
		{
		$this->Io->setCookie('cookieid', $this->User->getId(), (time() + $this->Settings->getValue('max_age')));
		$this->Io->setCookie('cookiepw', sha1($this->Settings->getValue('cookie_hash').sha1($this->Io->getString('password'))), (time() + $this->Settings->getValue('max_age')));
		}

	$this->Io->redirect('Forums');
	}

}

?>