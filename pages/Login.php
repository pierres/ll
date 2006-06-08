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

	if(!$this->Io->getEnv('HTTPS'))
		{
		$tls = '<br /><a href="https://'.$this->Io->getEnv('HTTP_HOST').'/?page=Login;id='.$this->Board->getId().'"><span class="button">TLS-Verschlüsselung</span></a> ';
		}
	else
		{
		$tls = '';
		}

	$this->addElement('forgot', $tls.'<a href="?page=ForgotPassword;id='.$this->Board->getId().'"><span class="button">Passwort vergessen?</span></a>');
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
		$this->Io->setCookie('cookiepw', sha1($this->Io->getString('password')), (time() + $this->Settings->getValue('max_age')));
		}

	$this->Io->redirect('Forums');
	}

}

?>