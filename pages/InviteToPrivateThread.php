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
class InviteToPrivateThread extends Form{

private $thread = 0;
private $newto	= array();
private $oldto	= array();

protected function setForm()
	{
	$this->setValue('title', 'Mitglieder einladen');

	if (!$this->User->isOnline())
		{
		$this->showFailure('nur für Mitglieder');
		}

	try
		{
		$this->thread = $this->Input->Request->getInt('thread');
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				thread_user
			WHERE
				threadid = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->thread);
		$stm->bindInteger($this->User->getId());
		$stm->getColumn();
		$stm->close();
		}
	catch (Exception $e)
		{
		$stm->close();
		$this->showFailure('Thema nicht gefunden');
		}

	$stm = $this->DB->prepare
		('
		SELECT
			users.id,
			users.name
		FROM
			users,
			thread_user
		WHERE
			thread_user.threadid = ?
			AND thread_user.userid = users.id
		');
	$stm->bindInteger($this->thread);

	$users = array();
	foreach ($stm->getRowSet() as $recipient)
		{
		$this->oldto[] = $recipient['id'];

		if ($recipient['id'] != $this->User->getId())
			{
			$users[] = '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$recipient['id'].'">'.$recipient['name'].'</a>';
			}
		}
	$stm->close();

	$this->addOutput('Schon dabei: '.implode(', ', $users).'<br /><br />');

	$this->addSubmit('Hinzufügen');
	$this->addHidden('thread', $this->thread);
	$this->addText('recipients', 'Neue Empfänger');
	$this->requires('recipients');
	}

protected function checkForm()
	{
	if ($this->Input->Request->isValid('recipients'))
		{
		$recipients = array_map('trim', explode(',', $this->Input->Request->getString('recipients')));

		try
			{
			foreach ($recipients as $recipient)
				{
				$user = AdminFunctions::getUserId($recipient);
				if (!in_array($user, $this->oldto) && !in_array($user, $this->newto) &&$user != $this->User->getId())
					{
					$this->newto[] = $user;
					}
				}
			}
		catch(DBNoDataException $e)
			{
			$this->showWarning('Empfänger "'.htmlspecialchars($recipient).'" ist unbekannt.');
			}

		if (empty($this->newto))
			{
			$this->showWarning('keine Empfänger angegeben.');
			}
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		INSERT INTO
			thread_user
		SET
			threadid = ?,
			userid = ?'
		);
	foreach ($this->newto as $user)
		{
		$stm->bindInteger($this->thread);
		$stm->bindInteger($user);
		$stm->execute();
		}
	$stm->close();

	$this->Output->redirect('PrivatePostings', 'thread='.$this->thread);
	}

}

?>