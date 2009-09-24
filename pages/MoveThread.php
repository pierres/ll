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

require('EditThread.php');

class MoveThread extends EditThread {

private $moveto 	= 0;
protected $title 	= 'Thema verschieben';


protected function setForm()
	{
	$this->setTitle($this->title);

	$this->checkInput();
	$this->checkAccess($this->forum);

	$this->buildList();
	}

protected function checkForm()
	{
	try
		{
		$this->moveto = $this->Input->Post->getInt('moveto');
		$this->checkAccess($this->moveto);
		}
	catch (RequestException $e)
		{
		$this->showWarning('Wohin damit?');
		}
	}

protected function checkAccess($forum = 0)
	{
	if ($this->User->isForumMod($forum))
		{
		return;
		}

	parent::checkAccess();
	}

protected function buildList()
	{
	$this->add(new SubmitButtonElement('Verschieben'));

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.id,
				forums.name
			FROM
				cats,
				forums,
				forum_cat
			WHERE
				cats.boardid = ?
				AND forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND forums.id <> ?
			ORDER BY
				cats.position,
				forum_cat.position
			');
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->forum);

		$radioInput = new RadioInputElement('moveto', 'Ziel');
		foreach ($stm->getRowSet() as $data)
			{
			$radioInput->addOption($data['name'], $data['id']);
			}
		$this->add($radioInput);

		$stm->close();
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
			threads
		SET
			forumid = ?,
			movedfrom = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->moveto);
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	AdminFunctions::updateForum($this->forum);
	// Auch das neue Forum muß aktualisiert werden
	AdminFunctions::updateForum($this->moveto);

	$this->redirect();
	}


protected function redirect()
	{
	$this->Output->redirect('Threads', array('forum' => $this->moveto));
	}

}

?>