<?php


class ForgotPassword extends Form{

private $name 	= '';
private $email 	= '';
private $id 	= 0;

protected function setForm()
	{
	$this->setValue('title', 'Passwort vergessen?');

	$this->addSubmit('Erinnern');

	$this->addText('name', 'Dein Name', '', 50);
	$this->requires('name');
	$this->setLength('name', 3, 25);

	$this->addText('email', 'Deine E-Mail-Adresse', '',  50);
	$this->requires('email');
	$this->setLength('email', 5, 50);
	}

protected function checkForm()
	{
	/** FIXME: Hier müssen noch einige Maßnahmen gegen Mißbrauch ergriffen werden */

	$this->name = $this->Io->getString('name');
	$this->email = $this->Io->getString('email');

	try
		{
		$this->id = $this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				users
			WHERE
				name = \''.$this->Sql->escapeString($this->name).'\'
				AND email = \''.$this->Sql->escapeString($this->email).'\'
			');

		}
	catch (SqlNoDataException $e)
		{
		$this->showWarning('Name und E-Mail wurden nicht gefunden.');
		}
	}

protected function sendForm()
	{
	/** FIXME: Generiere schönere Passwörter */
	$password = crypt(time(), crc32(microtime() ) );

	$this->Sql->query
		('
		UPDATE
			users
		SET
			password = \''.md5($this->Sql->escapeString($password)).'\'
		WHERE
			id = '.$this->id
		);

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom('support@laber-land.de');
	$this->Mail->setSubject('Dein Passwort im Laber-Land');
	$this->Mail->setText(
<<<eot
Hallo {$this->name}!

Dein Passwort lautet: {$password}
eot
);
	$this->Mail->send();

	$this->Io->redirect('Login');
	}
}

?>