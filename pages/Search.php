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
class Search extends Form{

private $search 	= '';
private $thread 	= 0;

protected function setForm()
	{
	$this->setValue('title', 'Suche');

	$this->addSubmit('Finden');

	$this->addText('search', 'Suchbegriff', '', 50);
	$this->requires('search');
	$this->setLength('search', 3, 50);

	$this->isCheckSecurityToken(false);
	$this->isCheckAntiSpamHash(false);
	}

protected function checkForm()
	{
	$this->search = $this->Io->getString('search');

	try
		{
		$this->thread = $this->Io->getInt('thread');
		}
	catch (IoRequestException $e)
		{
		$this->thread = 0;
		}
	}

private function getResult()
	{
	$result = array();

	$limit = $this->thread.','.$this->Settings->getValue('max_threads');

	try
		{
		$stm = $this->DB->prepare
		('
		(
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.posts,
				threads.lastuserid,
				threads.lastusername,
				threads.firstdate,
				threads.firstuserid,
				threads.firstusername,
				threads.closed,
				threads.sticky,
				threads.poll,
				threads.posts,
				(
					MATCH (threads.name) AGAINST (? IN BOOLEAN MODE)
					* (threads.lastdate + threads.firstdate)
				) AS score,
				forums.id AS forumid,
				forums.name AS forumname,
				threads.summary,
				tags.name AS tag
			FROM
				threads
					LEFT JOIN tags
					ON threads.tag = tags.id,
				forums
			WHERE MATCH
				(threads.name)
			AGAINST (? IN BOOLEAN MODE)
			AND threads.forumid = forums.id
			AND threads.deleted = 0
			AND forums.boardid = ?
		)
		UNION
		(
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.posts,
				threads.lastuserid,
				threads.lastusername,
				threads.firstdate,
				threads.firstuserid,
				threads.firstusername,
				threads.closed,
				threads.sticky,
				threads.poll,
				threads.posts,
				(
					MATCH (posts.text) AGAINST (? IN BOOLEAN MODE)
					* (threads.lastdate + threads.firstdate)
				) AS score,
				forums.id AS forumid,
				forums.name AS forumname,
				threads.summary,
				tags.name AS tag
			FROM
				posts,
				threads
					LEFT JOIN tags
					ON threads.tag = tags.id,
				forums
			WHERE MATCH
				(posts.text)
			AGAINST (? IN BOOLEAN MODE)
			AND posts.threadid = threads.id
			AND threads.forumid = forums.id
			AND threads.deleted = 0
			AND posts.deleted = 0
			AND forums.boardid = ?
			GROUP BY threads.id
		)
		ORDER BY score DESC
		LIMIT '.$limit
		);
		$stm->bindString($this->search);
		$stm->bindString($this->search);
		$stm->bindInteger($this->Board->getId());
		$stm->bindString($this->search);
		$stm->bindString($this->search);
		$stm->bindInteger($this->Board->getId());
		$result = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$this->showWarning('Leider nichts gefunden');
		}

	return $result;
	}

protected function sendForm()
	{
	$this->setValue('title', 'Suche nach &quot;'.htmlspecialchars($this->search).'&quot;');

	$params = ';search='.urlencode($this->search).';submit=';

	$next = '&nbsp;<a href="?page=Search;id='.$this->Board->getId().';thread='.($this->Settings->getValue('max_threads')+$this->thread).$params.'">&#187;</a>';

	$last = ($this->thread > 0 ? '<a href="?page=Search;id='.$this->Board->getId().';thread='.nat($this->thread-$this->Settings->getValue('max_threads')).$params.'">&#171;</a>' : '');

	$threads = $this->ThreadList->getList($this->getResult());

	if (count($this->warning) == 0)
		{
		$body =
		'<script type="text/javascript">
			/* <![CDATA[ */
			function writeText(text)
				{
				var pos;
				pos = document;
				while ( pos.lastChild && pos.lastChild.nodeType == 1 )
					pos = pos.lastChild;
				pos.parentNode.appendChild( document.createTextNode(text));
				}
			/* ]]> */
		</script>
		<table class="frame" style="width:100%">
			<tr>
				<td class="title" colspan="2">Thema</td>
				<td class="title">Erster Beitrag</td>
				<td class="title">Beiträge</td>
				<td class="title">Letzter Beitrag</td>
				<td class="title">Forum</td>
			</tr>
			<tr>
				<td class="pages" colspan="6">'.$last.$next.'&nbsp;</td>
			</tr>
			'.$threads.'
			<tr>
				<td class="pages" colspan="6">'.$last.$next.'&nbsp;</td>
			</tr>
		</table>
		';

		$this->appendOutput($body);
		}

	$this->showForm();
	}


}

?>