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
private $threads 	= 0;
private $tag		= 0;

protected function setForm()
	{
	$this->setValue('title', 'Suche');

	$this->addSubmit('Finden');

	$this->addText('search', 'Suchbegriff', '', 50);
	$this->requires('search');
	$this->setLength('search', 3, 50);

	$this->setTag();

	$this->isCheckSecurityToken(false);
	$this->isCheckAntiSpamHash(false);
	}

private function setTag()
	{
	$tags = array(' ' => '0');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				tags
			WHERE
				boardid = ?
			');
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $tag)
			{
			$tags[$tag['name']] = $tag['id'];
			}
		$stm->close();
		$this->addRadio('tag', 'Status', $tags, $this->tag);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->addHidden('tag', '0');
		}

	$this->requires('tag');
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

	$this->tag = $this->Io->getInt('tag');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				tags
			WHERE
				boardid = ?
				AND id = ?
			');
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->tag);

		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->tag != 0 && $this->showFailure('Ungültiger Status angegeben');
		}
	}

private function getResult()
	{
	try
		{
		if (!($result = $this->ObjectCache->getObject('LL:Search:'.$this->Board->getId().':'.$this->tag.':'.sha1($this->search))))
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
					(threads.name) AGAINST (? IN BOOLEAN MODE)
					AND threads.forumid = forums.id
					AND threads.deleted = 0
					'.($this->tag > 0 ? 'AND threads.tag = ?' : '').'
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
					(posts.text) AGAINST (? IN BOOLEAN MODE)
					AND posts.threadid = threads.id
					AND threads.forumid = forums.id
					AND threads.deleted = 0
					AND posts.deleted = 0
					'.($this->tag > 0 ? 'AND threads.tag = ?' : '').'
					AND forums.boardid = ?
					GROUP BY threads.id
			)
			ORDER BY score DESC
			LIMIT 500'
			);
			$stm->bindString($this->search);
			$stm->bindString($this->search);
			$this->tag > 0 && $stm->bindInteger($this->tag);
			$stm->bindInteger($this->Board->getId());
			$stm->bindString($this->search);
			$stm->bindString($this->search);
			$this->tag > 0 && $stm->bindInteger($this->tag);
			$stm->bindInteger($this->Board->getId());
			$result = $stm->getRowSet()->toArray();
			$this->ObjectCache->addObject('LL:Search:'.$this->Board->getId().':'.$this->tag.':'.sha1($this->search), gzcompress(serialize($result)), 5*60);
			}
		else
			{
			$result = unserialize(gzuncompress($result));
			}
		}
	catch (DBNoDataException $e)
		{
		$result = array();
		$this->showWarning('Leider nichts gefunden');
		}

	$this->threads = count($result);

	return $result;
	}

protected function sendForm()
	{
	$this->setValue('title', 'Suche nach &quot;'.htmlspecialchars($this->search).'&quot;');

	$threads = $this->ThreadList->getList(
			array_slice(
				$this->getResult(),
				$this->thread,
				$this->Settings->getValue('max_threads')
				)
				);

	$pages = $this->getPages();

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
				<td class="pages" colspan="5">'.$pages.'&nbsp;</td>
				<td class="pages">Ergebnis '.($this->thread+1).' bis '.($this->thread+$this->Settings->getValue('max_threads')).' von '.$this->threads.'</td>
			</tr>
			'.$threads.'
			<tr>
				<td class="pages" colspan="5">'.$pages.'&nbsp;</td>
				<td class="pages">Ergebnis '.($this->thread+1).' bis '.($this->thread+$this->Settings->getValue('max_threads')).' von '.$this->threads.'</td>
			</tr>
		</table>
		';

		$this->appendOutput($body);
		}

	$this->showForm();
	}

protected function getPages()
	{
	$params = ';search='.urlencode($this->search).';submit=;tag='.$this->tag;
	$pages = '';

	if ($this->thread > ($this->Settings->getValue('max_threads')))
		{
		$pages .= '<a href="?page='.$this->getName().';id='.$this->Board->getId().$params.'">&laquo;</a>';
		}

	if ($this->thread > 0)
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().$params.';thread='.nat($this->thread-$this->Settings->getValue('max_threads')).'">&lsaquo;</a>';
		}

	for ($i = 0; $i < ($this->threads / $this->Settings->getValue('max_threads')) && ($this->threads / $this->Settings->getValue('max_threads')) > 1; $i++)
		{
		if ($this->thread < $this->Settings->getValue('max_threads') * ($i-9))
			{
			$i = $this->Settings->getValue('max_threads') * ($i-4);
			continue;
			}
		elseif($this->thread > $this->Settings->getValue('max_threads') * ($i+9))
			{
			continue;
			}

		if ($this->thread == ($this->Settings->getValue('max_threads') * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().$params.';thread='.($this->Settings->getValue('max_threads') * $i).'">'.($i+1).'</a>';
			}
		}

	if ($this->threads > $this->Settings->getValue('max_threads')+$this->thread)
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().$params.';thread='.($this->Settings->getValue('max_posts')+$this->thread).'">&rsaquo;</a>';
		}

	$lastpage = $this->Settings->getValue('max_threads') *nat($this->threads / $this->Settings->getValue('max_threads'));

	if ($this->thread < $lastpage-$this->Settings->getValue('max_threads'))
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().$params.';thread='.$lastpage.'">&raquo;</a>';
		}

	return $pages;
	}


}

?>