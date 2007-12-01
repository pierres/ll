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

class EditTag extends Form{

private $thread		= 0;
private $tag		= 0;
private $forum		= 0;
private $post		= 0;

protected function setForm()
	{
	$this->setValue('title', 'Status ändern');
	$this->checkInput();
	$this->checkAccess();
	$this->addSubmit('Ändern');

	$tags = array(' ' => '0');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				tags
			WHERE
				boardid = ?
			');
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $tag)
			{
			$tags[$tag['name']] = $tag['id'];
			}
		$stm->close();
		$this->addRadio('tag', 'Status', $tags, $this->tag);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->addHidden('tag', '0');
		}
	
	$this->requires('tag');
	}

private function checkInput()
	{
	try
		{
		$this->thread = $this->Io->getInt('thread');
		$this->addHidden('thread', $this->thread);
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				posts.id,
				threads.forumid,
				threads.tag
			FROM
				posts JOIN threads ON threads.id = posts.threadid
			WHERE
				posts.deleted = 0
				AND threads.deleted = 0
				AND threads.closed = 0
				AND threads.id = ?
			ORDER BY
				posts.dat ASC
			');
		$stm->bindInteger($this->thread);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden oder geschlossen!');
		}

	$this->post = $data['id'];
	$this->forum = $data['forumid'];
	$this->tag = $data['tag'];
	}

protected function checkForm()
	{
	$this->tag = $this->Io->getInt('tag');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				tags
			WHERE
				boardid = ?
				AND id = ?
			');
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->tag);

		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->tag != 0 && $this->showFailure('Ungültiger Status angegeben');
		}
	}

private function checkAccess()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				posts
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->post);
		$access = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Kein Beitrag gefunden.');
		}

	if (!$this->User->isUser($access) && !$this->User->isForumMod($this->forum))
		{
		// Tun wir so, als wüssten wir von nichts
		$this->showFailure('Kein Beitrag gefunden.');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare(
		'
		UPDATE
			threads
		SET
			tag = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->tag);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>