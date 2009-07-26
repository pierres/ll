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
	$this->setTitle($this->L10n->getText('Register'));

	$this->add(new SubmitButtonElement($this->L10n->getText('Register')));

	$nameInput = new TextInputElement('name', '', $this->L10n->getText('Name'));
	$nameInput->setMinLength(3);
	$nameInput->setMaxLength(50);
	$nameInput->setSize(50);
	$nameInput->setFocus();
	$this->add($nameInput);

	$emailInput = new TextInputElement('email', '', $this->L10n->getText('e-mail address'));
	$emailInput->setMinLength(6);
	$emailInput->setMaxLength(50);
	$emailInput->setSize(50);
	$this->add($emailInput);
	}

protected function checkForm()
	{
	$this->name = $this->Input->Post->getString('name');
	$this->email = $this->Input->Post->getString('email');

	if (!$this->Mail->validateMail($this->email))
		{
		$this->showWarning($this->L10n->getText('e-mail address is invalid'));
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

		$this->showWarning($this->L10n->getText('An account with this name or e-mail address already exists'));
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
	$this->Mail->setSubject(sprintf($this->L10n->getText('Register at %s'), $this->Board->getName()));
	$this->Mail->setText(sprintf($this->L10n->getText(<<<eot
'Hello %s!

Thank you for your registration at "%s".
You can now set your password at the follwing website:
%s

User-ID:	%d
Key:		%s
eot
), $this->name, $this->Board->getName(), $this->Output->createUrl('ChangePasswordKey'), $userid, $key));
	$this->Mail->send();

 	$this->Output->redirect('Login');
	}

}


?>