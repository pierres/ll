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

class UserRecent extends ThreadList {


public function prepare()
	{
	$this->setTitle('Aktuelle Beiträge');
	$this->setValue('meta.robots', 'noindex,nofollow');

	try
		{
		$user = $this->Input->Get->getInt('user');
		}
	catch (RequestException $e)
		{
		$this->showWarning('Kein Benutzer angegeben!');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.posts,
				threads.lastusername,
				threads.firstdate,
				threads.firstusername,
				threads.closed,
				threads.sticky,
				threads.posts,
				threads.summary
			FROM
				threads,
				forums,
				posts,
				forum_cat,
				cats
			WHERE
				threads.deleted = 0
				AND posts.threadid = threads.id
				AND posts.userid = ?
				AND threads.forumid = forums.id
				AND forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND cats.boardid = ?
			GROUP BY
				threads.id
			ORDER BY
				threads.lastdate DESC
			LIMIT
				'.$this->Settings->getValue('max_threads')
			);

		$stm->bindInteger($user);
		$stm->bindInteger($this->Board->getId());
		$this->resultSet = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$this->resultSet = array();
		}

	$this->totalThreads = $this->Settings->getValue('max_threads');
	$body = $this->getBody();
	if (isset($stm))
		{
		$stm->close();
		}

	$this->setBody($body);
	}

}

?>