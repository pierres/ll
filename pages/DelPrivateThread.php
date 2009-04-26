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

class DelPrivateThread extends Form {

protected $thread		= 0;
private $deleted 		= false;

protected function setForm()
	{
	try
		{
		$this->thread = $this->Input->Get->getInt('thread');
		}
	catch (RequestException $e)
		{
		$this->showFailure('Kein Privates Thema angegeben!');
		}

	if (!$this->User->isOnline())
		{
		$this->showWarning('Nur für Mitglieder');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				deleted
			FROM
				threads
			WHERE
				id = ?
				AND firstuserid = ?
			');
		$stm->bindInteger($this->thread);
		$stm->bindInteger($this->User->getId());
		$result = $stm->getRow();
		$this->deleted = $result['deleted'];
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Privates Thema nicht gefunden!');
		}

	$this->setTitle('Privates Thema '.($this->deleted ? 'wiederherstellen' : 'löschen'));

	$this->setParam('thread', $this->thread);

	$this->add(new CheckboxInputElement('confirm', 'Bestätigung'));
	$this->add(new SubmitButtonElement($this->getTitle()));
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			deleted = ABS(deleted - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('PrivatePostings', array('thread' => $this->thread));
	}

}

?>