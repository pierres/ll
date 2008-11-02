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
		$this->showFailure('Nur für Mitglieder');
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
		$stm->bindInteger($this->Input->Request->getInt('thread'));
		$this->thread = $stm->getColumn();
		$stm->close();
		}
	catch (RequestException $e)
		{
		$stm->close();
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden!');
		}

	$this->addHidden('thread', $this->thread);
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
	$stm->bindInteger($this->time);
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
			smilies = ?,
			counter = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->User->getId());
	$stm->bindString($this->User->getName());
	$stm->bindString($this->text);
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->smilies ? 1 : 0);

	$stm->bindInteger($counter);

	$stm->execute();
	$stm->close();

	$insertid = $this->DB->getInsertId();
 	$this->DB->execute('UNLOCK TABLES');

	$this->sendFile($insertid);

	$this->updateThread($this->User->getId(), $this->User->getName());
	$this->updateBoard();

	$this->Log->insert($this->thread, $this->time);

	$this->redirect();
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
	$stm->bindInteger($this->time);
	$stm->bindInteger($userid);
	$stm->bindString($username);
	$stm->bindInteger($this->thread);

	$stm->execute();
	$stm->close();
	}

protected function updateForum($userid)
	{
	}

protected function redirect()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				name
			FROM
				threads
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->thread);
		$threadName = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$threadName = '';
		}

	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Beitrag geschrieben
				</td>
			</tr>
			<tr>
				<td class="main">
					Wohin darf es nun gehen?
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$this->thread.';post=-1#last">&#187; zurück zum Thema &quot;<em>'.$threadName.'</em>&quot;</a>
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=PrivateThreads;id='.$this->Board->getId().'">&#187; zurück zu den &quot;<em>Privaten Themen</em>&quot;</a>
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=Forums;id='.$this->Board->getId().'">&#187; zum Board &quot;<em>'.$this->Board->getName().'</em>&quot;</a>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Beitrag geschrieben');
	$this->setValue('body', $body);
	}

}


?>