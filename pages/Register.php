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

class Register extends Form {

private $email = '';
private $name = '';


protected function setForm()
	{
	$this->setTitle('Registrieren');

	$this->add(new SubmitButtonElement('Registrieren'));

	$nameInput = new TextInputElement('name', '', 'Dein Name');
	$nameInput->setMinLength(3);
	$nameInput->setMaxLength(50);
	$nameInput->setSize(50);
	$nameInput->setHelp('Dieser Name wird öffentlich angezeigt.');
	$nameInput->setFocus();
	$this->add($nameInput);

	$emailInput = new TextInputElement('email', '', 'Deine E-Mail-Adresse');
	$emailInput->setMinLength(6);
	$emailInput->setMaxLength(50);
	$emailInput->setSize(50);
	$emailInput->setHelp('Achte auf die Gültigkeit dieser Adresse, da die Zugangsdaten dorthin verschickt werden.');
	$this->add($emailInput);

	$privacyInput = new CheckboxInputElement('confirmPrivacy', 'Datenschutz');
	$privacyInput->setHelp('Bitte bestätige die <a href="'.$this->Output->createUrl('Privacy').'">Datenschutzerklärung</a>.');
	$this->add($privacyInput);
	}

protected function checkForm()
	{
	$this->name = $this->Input->Post->getString('name');
	$this->email = $this->Input->Post->getString('email');

	if (!$this->Mail->validateMail($this->email))
		{
		$this->showWarning('Keine gültige E-Mail-Adresse angegeben!');
		}

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
				OR email = ?
			');
		$stm->bindString(htmlspecialchars($this->name));
		$stm->bindString($this->email);
		$stm->getColumn();
		$stm->close();

		$this->showWarning('Name oder E-Mail bereits vergeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		INSERT INTO
			users
		SET
			name = ?,
			email = ?,
			password = ?,
			regdate = ?'
		);
	$stm->bindString(htmlspecialchars($this->name));
	$stm->bindString($this->email);
	$stm->bindString(sha1(generatePassword()));
	$stm->bindInteger($this->Input->getTime());
	$stm->execute();
	$stm->close();

	$key = generatePassword();
	$userid = $this->DB->getInsertId();

	$stm = $this->DB->prepare
		('
		DELETE FROM
			password_key
		WHERE
			id = ?'
		);
	$stm->bindInteger($userid);
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
	$stm->bindInteger($userid);
	$stm->bindString($key);
	$stm->bindInteger($this->Input->getTime());
	$stm->execute();
	$stm->close();

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom($this->Settings->getValue('email'));
	$this->Mail->setSubject('Registrierung bei '.$this->Board->getName());
	$this->Mail->setText(
'Hallo '.$this->name.'!

Deine Registrierung bei "'.$this->Board->getName().'" war erfolgreich.
Du kannst Dein Passwort ändern, wenn Du folgende Seite besuchst:
'.$this->Output->createUrl('ChangePasswordKey').'

Sollte obiger Link bei Deinem Mail-Programm nicht funktionieren,
so wähle im Anmelde-Dialog die Option "Passwort setzen" und gebe folgende Daten an:
Benutzer-ID:	'.$userid.'
Schlüssel:	'.$key.'



');
	$this->Mail->send();

 	$this->Output->redirect('Login');
	}

}


?>