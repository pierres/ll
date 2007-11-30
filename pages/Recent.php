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
class Recent extends Page{


public function prepare()
	{
	$this->setValue('title', 'Aktuelles');

	try
		{
		if ($this->User->isOnline())
			{
			/** TODO Potentiell teure Anfrage */
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
						0 AS forumid,
						0 AS forumname,
						threads.summary,
						tags.name AS tag
					FROM
						threads
							LEFT JOIN tags
							ON threads.tag = tags.id,
						thread_user
					WHERE
						threads.forumid = 0
						AND thread_user.threadid = threads.id
						AND threads.deleted = 0
						AND thread_user.userid = ?
					ORDER BY
						lastdate DESC
					LIMIT 50
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
						forums.id AS forumid,
						forums.name AS forumname,
						threads.summary,
						tags.name AS tag
					FROM
						forums,
						threads
							LEFT JOIN tags
							ON threads.tag = tags.id,
						forum_cat,
						cats
					WHERE
						threads.deleted = 0
						AND threads.forumid = forums.id
						AND forum_cat.forumid = forums.id
						AND forum_cat.catid = cats.id
						AND cats.boardid = ?
					ORDER BY
						lastdate DESC
					LIMIT 50
				)
				ORDER BY
					lastdate DESC
				LIMIT 50
				');
			$stm->bindInteger($this->User->getId());
			$stm->bindInteger($this->Board->getId());
			$result = $stm->getRowSet();
			}
		else
			{
			$stm = $this->DB->prepare
				('
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
					forums.id AS forumid,
					forums.name AS forumname,
					threads.summary,
					tags.name AS tag
				FROM
					forums,
					threads
						LEFT JOIN tags
						ON threads.tag = tags.id,
					forum_cat,
					cats
				WHERE
					threads.deleted = 0
					AND threads.forumid = forums.id
					AND forum_cat.forumid = forums.id
					AND forum_cat.catid = cats.id
					AND cats.boardid = ?
				ORDER BY
					threads.lastdate DESC
				LIMIT 50
				');
			$stm->bindInteger($this->Board->getId());
			$result = $stm->getRowSet();
			}
		}
	catch (DBNoDataException $e)
		{
		$result = array();
		}

	$threads = $this->ThreadList->getList($result);
	if (isset($stm))
		{
		$stm->close();
		}

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
			'.$threads.($this->User->isOnline() ?
			'<tr>
				<td class="cat" colspan="6"><a href="?page=MarkAllAsRead;id='.$this->Board->getId().'"><span class="button">Alles als <em>gelesen</em> markieren</span></a></td>
			</tr>' : '').'
		</table>
		';

	$this->setValue('body', $body);
	}

}

?>