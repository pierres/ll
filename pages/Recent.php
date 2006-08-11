<?php


class Recent extends Page{


public function prepare()
	{
	$this->setValue('title', 'Aktuelles');

	try
		{
		if ($this->User->isOnline())
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
						0 AS forumid,
						0 AS forumname,
						threads.summary
					FROM
						threads,
						thread_user
					WHERE
						threads.forumid = 0
						AND thread_user.threadid = threads.id
						AND threads.deleted = 0
						AND thread_user.userid = ?
					ORDER BY
						lastdate DESC
					LIMIT	25
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
						threads.summary
					FROM
						forums,
						threads
					WHERE
						threads.deleted = 0
						AND threads.forumid = forums.id
					ORDER BY
						lastdate DESC
					LIMIT	25
				)
				ORDER BY
					lastdate DESC
				LIMIT
					25
				');
			$stm->bindInteger($this->User->getId());
			$result = $stm->getRowSet();
			}
		else
			{
			$result = $this->DB->getRowSet
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
					threads.summary
				FROM
					forums,
					threads
				WHERE
					threads.deleted = 0
					AND threads.forumid = forums.id
				ORDER BY
					threads.lastdate DESC
				LIMIT
					25
				');
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
		'
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