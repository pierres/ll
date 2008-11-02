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
/** FIXME: Nicht geschützt via Form */
class SubmitPoll extends Page{

private $id 		= 0;
private $target 	= '';


public function prepare()
	{
	$this->target = ($this->Input->Request->isValid('target') ? $this->Input->Request->getString('target') : 'Postings');

	try
		{
		$this->id = $this->Input->Request->getInt('thread');
		}
	catch (RequestException $e)
		{
		$this->showWarning('Kein Thema angegeben.');
		}
	
	if ($this->Input->Request->isValid('result'))
		{
		$this->reload();
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forumid
			FROM
				threads
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$forum = $stm->getColumn();
		$stm->close();

		if ($forum == 0)
			{
			$stm = $this->DB->prepare
				('
				SELECT
					userid
				FROM
					thread_user
				WHERE
					userid = ?
					AND threadid = ?'
				);
			$stm->bindInteger($this->User->getId());
			$stm->bindInteger($this->id);
			$stm->getColumn();
			$stm->close();
			}

		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				polls
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$this->Output->setStatus(Output::NOT_FOUND);
		$this->setValue('meta.robots', 'noindex,nofollow');
		$this->showWarning('Keine Umfrage gefunden.');
		}
	}

protected function reload()
	{
	$this->Output->redirect($this->target, 'thread='.$this->id.($this->Input->Request->isValid('result') ? ';result' : ''));
	}

public function show()
	{
	if ($this->hasVoted())
		{
		$this->reload();
		}

	try
		{
		$valueid = $this->Input->Request->getInt('valueid');
		}
	catch (RequestException $e)
		{
		$this->reload();
		}

	$stm = $this->DB->prepare
		('
		INSERT INTO
			poll_voters
		SET
			pollid = ?,
			userid = ?'
		);
	$stm->bindInteger($this->id);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			poll_values
		SET
			votes = votes + 1
		WHERE
			id = ?
			AND pollid = ?'
		);
	$stm->bindInteger($valueid);
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();

	$this->reload();
	}

private function hasVoted()
	{
	if (!$this->User->isOnline())
		{
		return true;
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				poll_voters
			WHERE
				pollid = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->id);
		$stm->bindInteger($this->User->getId());
		$stm->getColumn();
		$stm->close();
		return true;
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return false;
		}
	}

}

?>