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
class ImportTags extends AdminPage{


public function prepare()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}
	set_time_limit(0);

	$this->DB->execute('LOCK TABLES threads WRITE, forums READ');

	$threads = $this->DB->getRowSet
		('
		SELECT
			threads.name,
			threads.id
		FROM
			threads,
			forums
		WHERE
			threads.name LIKE \'%gelöst%\'
			AND forums.boardid = 20
			AND threads.forumid = forums.id
		');
	
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			name = ?,
			tag = 1
		WHERE
			id = ?
		');

	$output = '';

	foreach ($threads as $thread)
		{
		$title = preg_replace('/\s*(&lt;|\[|\{|\()+\s*gelöst\s*(&gt;|\]|\}|\))+\s*/i', '', $thread['name']);
		if ($title != $thread['name'])
			{
			$stm->bindString($title);
			$stm->bindInteger($thread['id']);
			$stm->execute();
			$output .= $title.'<br />';
			}
		}
	$stm->close();
	
	$this->DB->execute('UNLOCK TABLES');

	$this->setValue('title', 'ImportTags');
	$this->setValue('body', $output);
	}


}

?>