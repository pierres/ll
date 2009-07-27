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

class PrivateThreads extends ThreadList {


public function prepare()
	{
	if (!$this->User->isOnline())
		{
		$this->showWarning($this->L10n->getText('Access denied!'));
		}

	$this->currentThread = nat($this->Input->Get->getInt('thread', 0));
	$limit = $this->currentThread.','. $this->Settings->getValue('max_threads');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				COUNT(*)
			FROM
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = ?'
			);
		$stm->bindInteger($this->User->getId());
		$this->totalThreads = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->totalThreads = 0;
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.lastusername,
				threads.firstusername,
				threads.posts,
				threads.deleted,
				threads.sticky,
				threads.closed,
				summary
			FROM
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = ?
				AND
					((thread_user.userid = threads.firstuserid
					AND threads.deleted = 1)
					OR threads.deleted = 0)
			ORDER BY
				lastdate DESC
			LIMIT
				'.$limit
			);
		$stm->bindInteger($this->User->getId());
		$this->resultSet = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$this->resultSet = array();
		}
	
	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('NewPrivateThread').'">'.$this->L10n->getText('Post new topic').'</a>');

	$this->setTitle($this->L10n->getText('Private topics'));
	$this->target = 'PrivatePostings';

	$this->setList();
	$stm->close();
	}
}

?>