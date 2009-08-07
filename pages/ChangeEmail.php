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

class ChangeEmail extends Form {

private $email;

protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->setTitle('E-Mail-Adresse ändern');

	$this->add(new SubmitButtonElement('Ändern'));

	$emailInput = new TextInputElement('email', '', 'Deine E-Mail-Adresse');
	$emailInput->setMinLength(6);
	$emailInput->setMaxLength(50);
	$emailInput->setSize(25);
	$emailInput->setHelp('Achte auf die Gültigkeit dieser Adresse,<br /> da die Zugangsdaten dorthin verschickt werden.');
	$this->add($emailInput);

	$confirmInput = new TextInputElement('confirm', '', 'Bestätige Deine E-Mail-Adresse');
	$confirmInput->setMinLength(6);
	$confirmInput->setMaxLength(50);
	$confirmInput->setSize(25);
	$this->add($confirmInput);
	}

protected function checkForm()
	{
	$this->email = $this->Input->Post->getString('email');

	if (!$this->Mail->validateMail($this->email))
		{
		$this->showWarning('Keine gültige E-Mail-Adresse angegeben!');
		}

	if ($this->email != $this->Input->Post->getString('confirm'))
		{
		$this->showWarning('Du hast Dich vertippt!');
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
				email = ?'
			);
		$stm->bindString($this->email);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return;
		}

	$this->showWarning('E-Mail-Adresse bereits vergeben!');
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			email = ?,
			password = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->email);
	$stm->bindString(sha1(generatePassword()));
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	$key = generatePassword();

	$stm = $this->DB->prepare
		('
		DELETE FROM
			password_key
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->User->getId());
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
	$stm->bindInteger($this->User->getId());
	$stm->bindString($key);
	$stm->bindInteger($this->Input->getTime());
	$stm->execute();
	$stm->close();

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom($this->Settings->getValue('email'));
	$this->Mail->setSubject('Dein Passwort bei '.$this->Board->getName());
	$this->Mail->setText(
'Hallo '.$this->User->getName().'!

Du kannst Dein Passwort ändern, wenn Du folgende Seite besuchst:
'.$this->Output->createUrl('ChangePasswordKey', array(), true, false).'

Sollte obiger Link bei Deinem Mail-Programm nicht funktionieren,
so wähle im Anmelde-Dialog die Option "Passwort setzen" und gebe folgende Daten an:
Benutzer-ID:	'.$this->User->getId().'
Schlüssel:	'.$key.'

');
	$this->Mail->send();

	$this->User->logout();

	$this->Output->redirect('Forums');
	}

}

?>