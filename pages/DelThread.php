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

class DelThread extends Form {

protected $forum 		= 0;
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
		$this->showFailure('Kein Thema angegeben');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forumid,
				deleted
			FROM
				threads
			WHERE
				closed = 0
				AND id = ?
			');
		$stm->bindInteger($this->thread);
		$result = $stm->getRow();
		$this->forum = $result['forumid'];
		$this->deleted = $result['deleted'];
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden oder geschlossen');
		}

	$this->setTitle('Thema '.($this->deleted ? 'wiederherstellen' : 'löschen'));

	$this->setParam('thread', $this->thread);

	$this->add(new CheckboxInputElement('confirm', 'Bestätigung'));
	$this->add(new SubmitButtonElement($this->getTitle()));
	}

protected function checkForm()
	{
	if (!$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Thema gefunden');
		}
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

	$this->updateForum();

	$this->redirect();
	}

protected function updateForum()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateForum($this->forum);
	}

protected function redirect()
	{
	$this->Output->redirect('Postings', array('thread' => $this->thread));
	}

}

?>