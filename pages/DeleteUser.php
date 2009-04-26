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

class DeleteUser extends Form {

private $user = 0;

protected function setForm()
	{
	$this->setTitle('Benutzerkonto löschen');

	try
		{
		$this->user = $this->Input->Get->getInt('user');
		}
	catch (RequestException $e)
		{
		$this->showFailure('Kein Benutzer angegeben');
		}

	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	if ($this->User->getID() != $this->user && !$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('Keine Berechtigung!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				name
			FROM
				users
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->user);
		$username = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Kein Benutzer gefunden');
		}

	$this->add(new SubmitButtonElement('Bestätigen'));
	$this->setParam('user', $this->user);

	$inputRadio = new CheckboxInputElement('confirm', 'Bestätigung');
	$inputRadio->setHelp('Benutzerkonto von <strong><a href="'.$this->Output->createUrl('ShowUser', array('user' => $this->user)).'">'.$username.'</a></strong> löschen.');
	$this->add($inputRadio);
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	AdminFunctions::delUser($this->user);

	if ($this->user == $this->User->getId())
		{
		$this->User->logout();
		}

	$this->Output->redirect('Forums');
	}

}


?>
