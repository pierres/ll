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
class DeleteUser extends Form{

private $user = 0;

protected function setForm()
	{
	try
		{
		$this->user = $this->Input->Request->getInt('user');
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

	$this->setValue('title', 'Benutzerkonto löschen');

	$this->addSubmit('Bestätigen');

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

	$this->addHidden('user', $this->user);

	$this->addElement('hint', 'Soll das Benutzerkonto von <strong><a href="?page=ShowUser;id='.$this->Board->getId().';user='.$this->user.'">'.$username.'</a></strong> wirklich gelöscht werden? <br />Alle Beiträge und angehängten Dateien bleiben erhalten.');

	$this->addRadio('confirm', 'Bestätige Deine Entscheidung',
	array('Ja, ich möchte dieses Benutzerkonto endgültig löschen.' => 1, 'Nein, lieber doch nichts löschen.' => 2), 2);
	$this->requires('confirm');
	$this->setLength('confirm', 1, 1);
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	if ($this->Input->Request->getInt('confirm') == 1)
		{
		AdminFunctions::delUser($this->user);

		if ($this->user == $this->User->getId())
			{
			$this->User->logout();
			}
		}

	$this->Output->redirect('Forums');
	}

}


?>
