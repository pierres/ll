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
class AdminDelBoard extends AdminForm{

protected function setForm()
	{
	$this->setValue('title', 'Board löschen');
	$this->addSubmit('Löschen');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				boards
			WHERE
				id <> ?
			ORDER BY
				name ASC
			');
		$stm->bindInteger($this->Board->getId());

		$radioArray = array();
		foreach ($stm->getRowSet() as $board)
			{
			$radioArray[$board['name']] = $board['id'];
			}
		$stm->close();
		
		$this->addRadio('board', 'Welches Board soll gelöscht werden?', $radioArray);
		$this->requires('board');
		$this->addCheckBox('sure', 'Mir ist klar, dass dadurch alle Daten verloren gehen.');
		$this->requires('sure');
		}
	catch (DBNoDataException $e)
		{
		}
	}

protected function sendForm()
	{
	set_time_limit(0);
	$this->DB->execute('LOCK TABLES
				attachments WRITE,
				attachment_thumbnails WRITE,
				boards WRITE,
				cats WRITE,
				forum_cat WRITE,
				forums WRITE,
				poll_values WRITE,
				poll_voters WRITE,
				polls WRITE,
				post_attachments WRITE,
				posts WRITE,
				thread_user WRITE,
				threads WRITE,
				threads_log WRITE,
				user_group WRITE,
				tags WRITE
			');
	AdminFunctions::delBoard($this->Input->Request->getInt('board'));
	$this->DB->execute('UNLOCK TABLES');

	$this->redirect();
	}


protected function redirect()
	{
	$this->Output->redirect('AdminDelBoard');
	}

}

?>