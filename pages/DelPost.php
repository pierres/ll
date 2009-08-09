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

class DelPost extends Form {

private $post = 0;
private $thread = 0;
private $forum = 0;
private $deleted = false;

protected function setForm()
	{
	try
		{
		$this->post = $this->Input->Get->getInt('post');
		}
	catch (RequestException $e)
		{
		$this->showFailure('Kein Beitrag angegeben');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.forumid,
				threads.id,
				posts.deleted
			FROM
				threads JOIN posts ON posts.threadid = threads.id
			WHERE
				threads.deleted = 0
				AND threads.closed = 0
				AND posts.id = ?
				AND threads.forumid <> 0
			');
		$stm->bindInteger($this->post);
		$data = $stm->getRow();
		$stm->close();

		$this->thread = $data['id'];
		$this->forum = $data['forumid'];
		$this->deleted = $data['deleted'];
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Beitrag nicht gefunden oder Thema geschlossen');
		}

	$this->setTitle('Beitrag '.($this->deleted ? 'wiederherstellen' : 'löschen'));

	$this->setParam('post', $this->post);

	$this->add(new CheckboxInputElement('confirm', 'Bestätigung'));
	$this->add(new SubmitButtonElement($this->getTitle()));
	}

protected function checkForm()
	{
	/** TODO: evtl. auch eigene Posts löschen */
	if (!$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Beitrag gefunden');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			deleted = ABS(deleted - 1)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	$this->updateThread();
	$this->updateForum();

	$this->redirect();
	}

protected function updateThread()
	{
	/** TODO: nicht optimal */
	AdminFunctions::updateThread($this->thread);
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