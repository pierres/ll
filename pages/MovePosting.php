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

class MovePosting extends Form {

private $moveto 	= 0;
private $post 		= 0;
private $forum 		= 0;
private $oldthread 	= 0;


protected function setForm()
	{
	$this->setTitle('Beitrag verschieben');

	try
		{
		$this->post = $this->Input->Get->getInt('post');
		$this->setParam('post', $this->post);
		}
	catch (RequestException $e)
		{
		$this->showWarning('Welcher Beitrag?');
		}

	$this->checkAccess();

	$this->buildList();
	}

protected function checkForm()
	{
	try
		{
		$this->moveto = $this->Input->Post->getInt('moveto');
 		$this->checkAccessMoveto();
		}
	catch (RequestException $e)
		{
		$this->showWarning('Wohin damit?');
		}
	}

protected function checkAccessMoveto()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.mods
			FROM
				threads,
				forums
			WHERE
				threads.forumid = forums.id
				AND threads.deleted = 0
				AND threads.closed = 0
				AND threads.id = ?
			');
		$stm->bindInteger($this->moveto);
		$mods = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
		}

	if (!$this->User->isMod() && !$this->User->isGroup($mods))
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}

protected function checkAccess()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.mods,
				forums.id,
				threads.id AS threadid
			FROM
				posts,
				threads,
				forums
			WHERE
				threads.id = posts.threadid
				AND threads.forumid = forums.id
				AND posts.deleted = 0
				AND threads.deleted = 0
				AND threads.closed = 0
				AND posts.id = ?
			');
		$stm->bindInteger($this->post);
		$forum = $stm->getRow();
		$stm->close();

		$this->forum = $forum['id'];
		$this->oldthread = $forum['threadid'];
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
		}

	if (!$this->User->isMod() && !$this->User->isGroup($forum['mods']))
		{
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}

protected function buildList()
	{
	$this->add(new SubmitButtonElement('Verschieben'));

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				threads
			WHERE
				id <> ?
				AND forumid = (SELECT forumid FROM threads WHERE id = ?)
				AND deleted = 0
				AND closed = 0
			ORDER BY
				lastdate DESC
			LIMIT 50
			');
		$stm->bindInteger($this->oldthread);
		$stm->bindInteger($this->oldthread);

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
			posts
		SET
			threadid = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->moveto);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	AdminFunctions::updateThread($this->oldthread);
	AdminFunctions::updateThread($this->moveto);
	AdminFunctions::updateForum($this->forum);

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('Threads', array('forum' => $this->forum));
	}

}

?>