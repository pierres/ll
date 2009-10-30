<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class GetRecent extends GetFile {

private $init = false;

public function prepare()
	{
	}

public function show()
	{
	try
		{
		$id = $this->Input->Get->getInt('id');
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
		$forum = $this->Input->Get->getInt('forum');
		}
	catch (RequestException $e)
		{
		$forum = 0;
		}

	try
		{
		$showContent = $this->Input->Get->getInt('content') > 0;
		}
	catch (RequestException $e)
		{
		$showContent = false;
		}

	if (!($content = $this->ObjectCache->getObject('LL:GetRecent:Atom:'.$id.':'.$forum.':'.$showContent)))
		{
		!$this->init && $this->initDB();

		$lastdate = 0;
		$entries = '';

		try
			{
			if ($forum == 0)
				{
				if ($showContent)
					{
					$stm = $this->DB->prepare
						('
						SELECT
							threads.id,
							threads.name,
							threads.firstdate,
							threads.firstusername,
							threads.firstuserid,
							threads.summary,
							posts.text
						FROM
							threads,
							forum_cat,
							cats,
							posts
						WHERE
							threads.deleted = 0
							AND forum_cat.forumid = threads.forumid
							AND forum_cat.catid = cats.id
							AND cats.boardid = ?
							AND posts.threadid = threads.id
							AND posts.counter = 0
						ORDER BY
							threads.firstdate DESC
						LIMIT
							25
						');
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
					}
				$stm->bindInteger($id);
				}
			else
				{
				if ($showContent)
					{
					$stm = $this->DB->prepare
						('
						SELECT
							threads.id,
							threads.name,
							threads.firstdate,
							threads.firstusername,
							threads.firstuserid,
							threads.summary,
							posts.text
						FROM
							threads,
							posts
						WHERE
							threads.deleted = 0
							AND threads.forumid = ?
							AND threads.forumid > 0
							AND posts.threadid = threads.id
							AND posts.counter = 0
						ORDER BY
							threads.firstdate DESC
						LIMIT
							25
						');
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
					}
				$stm->bindInteger($forum);
				}

			foreach($stm->getRowSet() as $thread)
				{
				if ($thread['firstdate'] > $lastdate)
					{
					$lastdate = $thread['firstdate'];
					}
				
				if ($showContent)
					{
					# FIXME ugly code
					# make relative URLs absolute
					$thread['text'] = str_replace('<a href="?', '<a href="'.$this->Input->getPath().'?', $thread['text']);
					$thread['text'] = str_replace('<img src="?', '<img src="'.$this->Input->getPath().'?', $thread['text']);
					$thread['text'] = str_replace('<img src="images/smilies/', '<img src="'.$this->Input->getPath().'images/smilies/', $thread['text']);
					}

				$entries .=
				'
				<entry>
					<id>'.$this->Output->createUrl('Postings', array('thread' => $thread['id']), true).'</id>
					<title>'.$thread['name'].'</title>
					<link rel="alternate" type="text/html" href="'.$this->Output->createUrl('Postings', array('thread' => $thread['id'], 'post' => '-1'), true).'" />
					<updated>'.date('c', $thread['firstdate']).'</updated>
					<summary>'.$thread['summary'].'</summary>
					'.($showContent ? '<content type="html"><![CDATA['.$thread['text'].']]></content>' : '').'
					<author>
						<name>'.$thread['firstusername'].'</name>
						<uri>'.$this->Output->createUrl('ShowUser', array('user' => $thread['firstuserid']), true).'</uri>
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
	<id>'.$this->Output->createUrl('GetRecent', array(), true).'</id>
	<title>'.$this->Board->getName().'</title>
	<link rel="self" type="application/atom+xml" href="'.$this->Output->createUrl('GetRecent', array(), true).'" />
	<link rel="alternate" type="text/html" href="'.$this->Output->createUrl('Forums', array(), true).'" />
	<updated>'.date('c', $lastdate).'</updated>
	'.$entries.'
</feed>';

		$this->ObjectCache->addObject('LL:GetRecent:Atom:'.$id.':'.$forum.':'.$showContent, $content, 15*60);
		}

	$this->compression = true;
	$this->sendInlineFile('application/atom+xml; charset=UTF-8', 'recent.xml', $content);
	}

}

?>