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

class ForgotPassword extends Form {

private $name 	= '';
private $email 	= '';
private $id 	= 0;

protected function setForm()
	{
	$this->setTitle('Passwort vergessen?');

	$this->add(new SubmitButtonElement('Erinnern'));

	$inputName = new TextInputElement('name', '', 'Dein Name');
	$inputName->setSize(25);
	$inputName->setMinLength(3);
	$inputName->setMaxLength(25);
	$this->add($inputName);

	$inputEmail = new TextInputElement('email', '', 'Deine E-Mail-Adresse');
	$inputEmail->setSize(25);
	$inputEmail->setMinLength(5);
	$inputEmail->setMaxLength(50);
	$this->add($inputEmail);
	}

protected function checkForm()
	{
	$this->name = $this->Input->Post->getHtml('name');
	$this->email = $this->Input->Post->getString('email');

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
	$stm->bindInteger($this->Input->getTime());
	$stm->execute();
	$stm->close();

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom($this->Settings->getValue('email'));
	$this->Mail->setSubject('Dein Passwort bei '.$this->Board->getName());
	$this->Mail->setText(
'Hallo '.$this->name.'!

Du kannst Dein Passwort ändern, wenn Du folgende Seite besuchst:
'.$this->Output->createUrl('ChangePasswordKey', array(), true, false).'

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