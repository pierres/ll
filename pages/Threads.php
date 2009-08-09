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

class Threads extends ThreadList {

protected $ismod = false;
protected $forum = 0;


public function prepare()
	{
	try
		{
		$this->forum = $this->Input->Get->getInt('forum');
		}
	catch (RequestException $e)
		{
		$this->showFailure($this->L10n->getText('No forum specified'));
		}

	$this->currentThread = $this->Input->Get->getInt('thread', 0);

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.name,
				cats.name AS catname,
				cats.id AS catid,
				forums.mods
			FROM
				forums,
				forum_cat,
				cats
			WHERE
				forums.id = ?
				AND forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND cats.boardid = ?'
			);
		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->Board->getId());
		$forum = $stm->getRow();
		$stm->close();
		}
	catch (DBException $e)
		{
		$stm->close();
		$this->Output->setStatus(Output::NOT_FOUND);
		$this->showFailure($this->L10n->getText('Forum not found'));
		}

	$this->ismod = $this->User->isGroup($forum['mods']) || $this->User->isMod();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				COUNT(*)
			FROM
				threads
			WHERE
				forumid = ?
			');
		$stm->bindInteger($this->forum);
		$this->totalThreads = $stm->getColumn();
		$stm->close();
		}
	catch (DBException $e)
		{
		$stm->close();
		$this->totalThreads = 0;
		}

	try
		{
		$stm = $this->DB->prepare
			('
			(
				SELECT
					threads.id,
					threads.name,
					threads.lastdate,
					threads.lastusername,
					threads.firstusername,
					threads.closed,
					threads.sticky,
					threads.deleted,
					threads.posts,
					threads.summary
				FROM
					threads
				WHERE
					threads.forumid = ?
					'.($this->ismod ? '' : 'AND threads.deleted =  0').'
					AND threads.sticky = 1
			)
			UNION
			(
				SELECT
					threads.id,
					threads.name,
					threads.lastdate,
					threads.lastusername,
					threads.firstusername,
					threads.closed,
					threads.sticky,
					threads.deleted,
					threads.posts,
					threads.summary
				FROM
					threads
				WHERE
					threads.forumid = ?
					'.($this->ismod ? '' : 'AND threads.deleted =  0').'
					AND threads.counter BETWEEN ? AND ?
					AND threads.sticky = 0
			)
			ORDER BY
				sticky DESC,
				lastdate DESC
			');
		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->forum);

		$stm->bindInteger($this->totalThreads-$this->Settings->getValue('max_threads')-$this->currentThread);
		$stm->bindInteger($this->totalThreads-$this->currentThread-1);

		$this->resultSet = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$this->resultSet = array();
		}
	
	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('NewThread', array('forum' => $this->forum)).'">'.$this->L10n->getText('Post new topic').'</a>');

	if ($this->User->isOnline())
		{
		$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('MarkAsRead', array('forum' => $this->forum)).'">'.$this->L10n->getText('Mark forum as read').'</a>');
		}

	$this->setTitle($forum['name']);

	$this->pageOptions = array('forum' => $this->forum); 

	$this->setList();
	$stm->close();
	}

}

?>