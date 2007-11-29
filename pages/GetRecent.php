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
class GetRecent extends GetFile{

public function prepare()
	{
	$this->initDB();
	}

public function show()
	{
	$entries = '';

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.lastusername,
				threads.lastuserid,
				threads.summary
			FROM
				threads,
				forum_cat,
				cats
			WHERE
				threads.deleted = 0
				AND forum_cat.forumid = threads.forumid
				AND forum_cat.catid = cats.id
				AND cats.boardid = ?
			ORDER BY
				threads.lastdate DESC
			LIMIT
				25
			');
		$stm->bindInteger($this->Board->getId());

		$lastdate = 0;

		foreach($stm->getRowSet() as $thread)
			{
			if ($thread['lastdate'] > $lastdate)
				{
				$lastdate = $thread['lastdate'];
				}

			$entries .=
			'
			<entry>
				<id>'.$this->Io->getURL().'?page=Postings;id='.$this->Board->getId().';thread='.$thread['id'].'</id>
				<title>'.$thread['name'].'</title>
				<link rel="alternate" type="text/html" href="'.$this->Io->getURL().'?page=Postings;id='.$this->Board->getId().';thread='.$thread['id'].';post=-1" />
				<updated>'.date('c', $thread['lastdate']).'</updated>
				<summary>'.$thread['summary'].'</summary>
				<author>
					<name>'.$thread['lastusername'].'</name>
					<uri>'.$this->Io->getURL().'?page=ShowUser;id='.$this->Board->getId().';user='.$thread['lastuserid'].'</uri>
				</author>
			</entry>
			';
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}


	$content =
'<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="de">
	<id>'.$this->Io->getURL().'?page=GetRecent;id='.$this->Board->getId().'</id>
	<title>'.$this->Board->getName().'</title>
	<link rel="self" type="application/atom+xml" href="'.$this->Io->getURL().'?page=GetRecent;id='.$this->Board->getId().'" />
	<link rel="alternate" type="text/html" href="'.$this->Io->getURL().'?page=Forums;id='.$this->Board->getId().'" />
	<updated>'.date('c', $lastdate).'</updated>
	'.$entries.'
</feed>';

	$this->sendInlineFile('application/atom+xml; charset=UTF-8', 'recent.xml', strlen($content), $content);
	}

}

?>