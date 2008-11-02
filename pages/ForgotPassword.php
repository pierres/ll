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
class ForgotPassword extends Form{

private $name 	= '';
private $email 	= '';
private $id 	= 0;

protected function setForm()
	{
	$this->setValue('title', 'Passwort vergessen?');

	$this->addSubmit('Erinnern');

	$this->addText('name', 'Dein Name', '', 25);
	$this->requires('name');
	$this->setLength('name', 3, 25);

	$this->addText('email', 'Deine E-Mail-Adresse', '',  25);
	$this->requires('email');
	$this->setLength('email', 5, 50);
	}

protected function checkForm()
	{
	$this->name = $this->Input->Request->getHtml('name');
	$this->email = $this->Input->Request->getString('email');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				users
			WHERE
				name = ?
				AND email = ?
			');
		$stm->bindString($this->name);
		$stm->bindString($this->email);
		$this->id = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Name und E-Mail wurden nicht gefunden.');
		}
	}

protected function sendForm()
	{
	$key = generatePassword();

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
		INSERT INTO
			password_key
		SET
			id = ?,
			`key` = ?,
			request_time = ?'
		);
	$stm->bindInteger($this->id);
	$stm->bindString($key);
	$stm->bindInteger(time());
	$stm->execute();
	$stm->close();

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom($this->Settings->getValue('email'));
	$this->Mail->setSubject('Dein Passwort bei '.$this->Board->getName());
	$this->Mail->setText(
'Hallo '.$this->name.'!

Du kannst Dein Passwort ändern, wenn Du folgende Seite besuchst:
'.$this->Input->getURL().'?id='.$this->Board->getId().';page=ChangePasswordKey;userid='.$this->id.';key='.$key.'

Sollte obiger Link bei Deinem Mail-Programm nicht funktionieren,
so wähle im Anmelde-Dialog die Option "Passwort setzen" und gebe folgende Daten an:
Benutzer-ID:	'.$this->id.'
Schlüssel:	'.$key.'

Solltest Du Dir diese Erinnerung nicht geschickt haben,
so kannst Du diese Nachricht ignorieren.
Dein altes Passwort bleibt dann weiterhin gültig.');
	$this->Mail->send();

	$this->Output->redirect('Login');
	}
}

?>