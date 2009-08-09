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

class AdminDelBoard extends AdminForm {


protected function setForm()
	{
	$this->setTitle('Board löschen');
	$this->add(new SubmitButtonElement('Löschen'));

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff');
		}

	$boardInput = new RadioInputElement('board', 'Welches Board soll gelöscht werden?');

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

		foreach ($stm->getRowSet() as $board)
			{
			$boardInput->addOption($board['name'], $board['id']);
			}
		$stm->close();

		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->add($boardInput);
	$this->add(new CheckboxInputElement('sure', 'Mir ist klar, dass dadurch alle Daten verloren gehen.'));
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
				user_group WRITE
			');
	AdminFunctions::delBoard($this->Input->Post->getInt('board'));
	$this->DB->execute('UNLOCK TABLES');

	$this->Output->redirect('AdminDelBoard');
	}

}

?>