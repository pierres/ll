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

private $init = false;

public function prepare()
	{
	}

public function show()
	{
	try
		{
		$id = $this->Input->Request->getInt('id');
		}
	catch (RequestException $e)
		{
		// ok, we have to initialize the Board module then...
		$this->initDB();
		$this->init = true;
		$id = $this->Board->getId();
		}

	try
		{
		$forum = $this->Input->Request->getInt('forum');
		}
	catch (RequestException $e)
		{
		$forum = 0;
		}

	if (!($content = $this->ObjectCache->getObject('LL:GetRecent:Atom:'.$id.':'.$forum)))
		{
		!$this->init && $this->initDB();

		$lastdate = 0;
		$entries = '';

		try
			{
			if ($forum == 0)
				{
				$stm = $this->DB->prepare
					('
					SELECT
						threads.id,
						threads.name,
						threads.firstdate,
						threads.firstusername,
						threads.firstuserid,
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
						threads.firstdate DESC
					LIMIT
						25
					');
				$stm->bindInteger($id);
				}
			else
				{
				$stm = $this->DB->prepare
					('
					SELECT
						threads.id,
						threads.name,
						threads.firstdate,
						threads.firstusername,
						threads.firstuserid,
						threads.summary
					FROM
						threads
					WHERE
						threads.deleted = 0
						AND threads.forumid = ?
						AND threads.forumid > 0
					ORDER BY
						threads.firstdate DESC
					LIMIT
						25
					');
				$stm->bindInteger($forum);
				}

			foreach($stm->getRowSet() as $thread)
				{
				if ($thread['firstdate'] > $lastdate)
					{
					$lastdate = $thread['firstdate'];
					}

				$entries .=
				'
				<entry>
					<id>'.$this->Input->getURL().'?page=Postings;id='.$id.';thread='.$thread['id'].'</id>
					<title>'.$thread['name'].'</title>
					<link rel="alternate" type="text/html" href="'.$this->Input->getURL().'?page=Postings;id='.$id.';thread='.$thread['id'].';post=-1" />
					<updated>'.date('c', $thread['firstdate']).'</updated>
					<summary>'.$thread['summary'].'</summary>
					<author>
						<name>'.$thread['firstusername'].'</name>
						<uri>'.$this->Input->getURL().'?page=ShowUser;id='.$id.';user='.$thread['firstuserid'].'</uri>
					</author>
				</entry>
				';
				}
			}
		catch (DBNoDataException $e)
			{
			}

		if (isset($stm))
			{
			$stm->close();
			}

		$content =
'<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="de">
	<id>'.$this->Input->getURL().'?page=GetRecent;id='.$id.'</id>
	<title>'.$this->Board->getName().'</title>
	<link rel="self" type="application/atom+xml" href="'.$this->Input->getURL().'?page=GetRecent;id='.$id.'" />
	<link rel="alternate" type="text/html" href="'.$this->Input->getURL().'?page=Forums;id='.$id.'" />
	<updated>'.date('c', $lastdate).'</updated>
	'.$entries.'
</feed>';

		$this->ObjectCache->addObject('LL:GetRecent:Atom:'.$id.':'.$forum, $content, 15*60);
		}

	$this->sendInlineFile('application/atom+xml; charset=UTF-8', 'recent.xml', strlen($content), $content);
	}

}

?>