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

class SplitThread extends Form {

private $post 		= 0;
private $oldthread 	= 0;
private $newthread 	= 0;
private $forum	 	= 0;
private $newtopic 	= '';


protected function setForm()
	{
	$this->setTitle($this->L10n->getText('Split topic'));

	try
		{
		$this->post = $this->Input->Get->getInt('post');
		}
	catch (RequestException $e)
		{
		$this->showWarning($this->L10n->getText('No post specified'));
		}

	$this->checkAccess();

	try
		{
		$this->newtopic = $this->Input->Post->getString('newtopic');
		}
	catch (RequestException $e)
		{
		}

	$this->add(new SubmitButtonElement($this->L10n->getText('Submit')));

	$textInput = new TextInputElement('newtopic', $this->newtopic, $this->L10n->getText('New topic'));
	$textInput->setMinLength(3);
	$textInput->setMaxLength(100);
	$this->add($textInput);

	$this->setParam('post', $this->post);
	}

protected function checkForm()
	{
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
				AND threads.firstdate <> posts.dat
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
		$this->showFailure($this->L10n->getText('Topic not found'));
		}

	if (!$this->User->isMod() && !$this->User->isGroup($forum['mods']))
		{
		$this->showFailure($this->L10n->getText('Topic not found'));
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		INSERT INTO
			threads
		SET
			name = ?,
			forumid = ?
		');
	$stm->bindString(htmlspecialchars($this->newtopic));
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();

	$this->newthread = $this->DB->getInsertId();

	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			threads = threads + 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			threadid = ?
		WHERE
			threadid = ?
			AND id >= ?'
		);
	$stm->bindInteger($this->newthread);
	$stm->bindInteger($this->oldthread);
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	AdminFunctions::updateThread($this->oldthread);
	AdminFunctions::updateThread($this->newthread);
	AdminFunctions::updateForum($this->forum);

	$this->sendThreadSummary();

	$this->redirect();
	}

protected function sendThreadSummary()
	{
	$stm = $this->DB->prepare
		('
		SELECT
			text
		FROM
			posts
		WHERE
			id = ?
		');
	$stm->BindInteger($this->post);
	$text = $stm->GetColumn();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			summary = ?
		WHERE
			id = ?
		');

	$stm->bindString($this->UnMarkup->fromHtmlToText($text));
	$stm->bindInteger($this->newthread);
	$stm->execute();
	$stm->close();
	}

protected function redirect()
	{
	$this->Output->redirect('Threads', array('forum' => $this->forum));
	}

}

?>