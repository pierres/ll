<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class AdminRenameUser extends AdminForm {

private $userid = 0;
private $currentname = '';
private $newname = '';

protected function setForm()
	{
	$this->setTitle('Benutzer umbenennen');
	$this->add(new SubmitButtonElement('Abschicken'));

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff');
		}

	$currentnameInput = new TextInputElement('currentname', '', 'Aktueller Name');
	$currentnameInput->setMinLength(3);
	$currentnameInput->setMaxLength(25);
	$currentnameInput->setSize(25);
	$this->add($currentnameInput);

	$newnameInput = new TextInputElement('newname', '', 'Neuer Name');
	$newnameInput->setMinLength(3);
	$newnameInput->setMaxLength(25);
	$newnameInput->setSize(25);
	$this->add($newnameInput);
	}

protected function checkForm()
	{
	$this->currentname = $this->Input->Post->getString('currentname');
	$this->newname = $this->Input->Post->getString('newname');

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
			');
		$stm->bindString(htmlspecialchars($this->currentname));
		$this->userid = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Aktueller Benutzername existiert nicht');
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
			');
		$stm->bindString(htmlspecialchars($this->newname));
		$stm->getColumn();
		$stm->close();

		$this->showWarning('Neuer Benutzername existiert bereits');
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
		UPDATE
			users
		SET
			name = ?
		WHERE
			id = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			username = ?
		WHERE
			userid = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			session
		SET
			name = ?
		WHERE
			id = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			lastusername = ?
		WHERE
			lastuserid = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			firstusername = ?
		WHERE
			firstuserid = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$this->Output->redirect('AdminSettings');
	}

}

?>