<?php

class Register extends Form{

private $email = '';
private $name = '';


protected function setForm()
	{
	$this->setValue('title', 'Registrieren');

	$this->addSubmit('Registrieren');

	$this->addText('name', 'Dein Name', '', 50);
	$this->requires('name');
	$this->setLength('name', 3, 25);

	$this->addText('email', 'Deine E-Mail-Adresse', '', 50);
	$this->requires('email');
	$this->setLength('email', 6, 50);

	$this->addElement('hint', 'Achte auf die Gültigkeit dieser Adresse,<br /> da die Zugangsdaten dorthin verschickt werden.');
	}

protected function checkForm()
	{
	$this->name = $this->Io->getString('name');
	$this->email = $this->Io->getString('email');

	if (!$this->Mail->validateMail($this->email))
		{
		$this->showWarning('Keine gültige E-Mail-Adresse angegeben!');
		}

	try
		{
		 $this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				users
			WHERE
				name = \''.$this->Sql->formatString($this->name).'\'
				OR email = \''.$this->Sql->escapeString($this->email).'\'
			');

		$this->showWarning('Name oder E-Mail bereits vergeben!');
		}
	catch (SqlNoDataException $e)
		{
		}
	}

protected function sendForm()
	{
	$password = crypt(time(), crc32(microtime() ) );

	$this->Sql->query
		('
		INSERT INTO
			users
		SET
			name = \''.$this->Sql->formatString($this->name).'\',
			email = \''.$this->Sql->escapeString($this->email).'\',
			password = \''.md5($password).'\',
			regdate = '.time()
		);

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom('support@laber-land.de');
	$this->Mail->setSubject('Registrierung im Laber-Land');
	$this->Mail->setText(
<<<eot
Hallo {$this->name}!

Deine Registrierung bei www.laber-land.de war erfolgreich.

Dein Passwort lautet: {$password}
eot
);
	$this->Mail->send();


	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Registrierung erfolgreich
				</td>
			</tr>
			<tr>
				<td class="main">
					Willkommen im Laber-Land, '.htmlspecialchars($this->name).'!
					<p>
					Dein Passwort wurde Dir soeben an <em>'.htmlspecialchars($this->email).'</em> geschickt. Mit diesem kannst Du Dich nun <a href="?page=Login;id='.$this->Board->getId().'" class="button">Anmelden</a>.
					</p>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Registrierung erfolgreich');
	$this->setValue('body', $body);
	}


}


?>