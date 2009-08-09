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
require('NewPost.php');

class EditPost extends NewPost {

protected $post 	= 0;
protected $title 	= 'Beitrag bearbeiten';


protected function checkInput()
	{
	try
		{
		$this->post = $this->Input->Get->getInt('post');
		$this->setParam('post', $this->post);
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
				posts.text,
				posts.threadid,
				threads.forumid
			FROM
				posts JOIN threads ON threads.id = posts.threadid
			WHERE
				posts.id = ?
				AND posts.deleted = 0
				AND threads.deleted = 0
				AND threads.closed = 0
			');
		$stm->bindInteger($this->post);
		$data = $stm->getRow();
		$stm->close();

		$this->text =  $this->UnMarkup->fromHtml($data['text']);
		$this->thread = $data['threadid'];
		$this->forum = $data['forumid'];
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Beitrag nicht gefunden oder Thema geschlossen');
		}
	}

protected function checkAccess()
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
		$this->showFailure('Kein Beitrag gefunden');
		}

	if (!$this->User->isUser($access) && !$this->User->isForumMod($this->forum))
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
			text = ?,
			editdate = ?,
			editby = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->text);
	$stm->bindInteger($this->Input->getTime());
	$stm->bindInteger($this->User->getId());
	$stm->bindInteger($this->post);
	$stm->execute();
	$stm->close();

	$this->sendFile($this->post);

	$this->redirect();
	}

protected function sendFile($postid)
	{
	if($this->User->isOnline() && $this->Input->Post->isString('addfile'))
		{
		$stm = $this->DB->prepare
			('
			DELETE FROM
				post_attachments
			WHERE
				postid = ?'
			);
		$stm->bindInteger($postid);
		$stm->execute();
		$stm->close();

		$stm = $this->DB->prepare
			('
			UPDATE
				posts
			SET
				file = 0
			WHERE
				id = ?'
			);
		$stm->bindInteger($postid);
		$stm->execute();
		$stm->close();

		parent::sendFile($postid);
		}
	}

}

?>