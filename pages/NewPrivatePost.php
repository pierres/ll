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

class NewPrivatePost extends NewPost {

public function __construct()
	{
	parent::__construct();

	if (!$this->User->isOnline())
		{
		$this->showFailure($this->L10n->getText('Access denied'));
		}
	}

protected function checkInput()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id
			FROM
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = ?
				AND threads.id = ?'
			);
		$stm->bindInteger($this->User->getId());
		$stm->bindInteger($this->Input->Get->getInt('thread'));
		$this->thread = $stm->getColumn();
		$stm->close();
		}
	catch (RequestException $e)
		{
		$stm->close();
		$this->showFailure($this->L10n->getText('No topic specified'));
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure($this->L10n->getText('Topic not found'));
		}

	$this->setParam('thread', $this->thread);
	}

protected function checkAccess()
	{
	/** Privater Thread -> Prüfung */
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			posts = posts + 1,
			lastpost = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Input->getTime());
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

 	$this->DB->execute('LOCK TABLES posts WRITE');

	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			posts
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($this->thread);
	$counter = $stm->getColumn();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			posts
		SET
			threadid = ?,
			userid = ?,
			username = ?,
			text = ?,
			dat = ?,
			counter = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->User->getId());
	$stm->bindString($this->User->getName());
	$stm->bindString($this->text);
	$stm->bindInteger($this->Input->getTime());
	$stm->bindInteger($counter);

	$stm->execute();
	$stm->close();

	$insertid = $this->DB->getInsertId();
 	$this->DB->execute('UNLOCK TABLES');

	$this->sendFile($insertid);

	$this->updateThread($this->User->getId(), $this->User->getName());
	$this->updateBoard();

	$this->Log->insert($this->thread, $this->Input->getTime());

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('PrivatePostings', array('thread' => $this->thread, 'post' => -1));	
	}

protected function updateThread($userid, $username)
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			lastdate = ?,
			lastuserid = ?,
			lastusername = ?,
			posts = posts + 1
		WHERE
			id = ?
		');
	$stm->bindInteger($this->Input->getTime());
	$stm->bindInteger($userid);
	$stm->bindString($username);
	$stm->bindInteger($this->thread);

	$stm->execute();
	$stm->close();
	}

protected function updateForum($userid)
	{
	}

}


?>