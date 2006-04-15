<?php


class Recent extends Search{


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
						(SELECT text FROM posts WHERE threadid = threads.id AND dat = threads.firstdate) AS summary
					FROM
						threads,
						thread_user
					WHERE
						threads.forumid = 0
						AND threads.deleted = 0
						AND thread_user.threadid = threads.id
						AND thread_user.userid = ?
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
						(SELECT text FROM posts WHERE threadid = threads.id AND dat = threads.firstdate) AS summary
					FROM
						forums,
						threads
					WHERE
						threads.forumid = forums.id
						AND threads.deleted = 0
				)
				ORDER BY
					lastdate DESC
				LIMIT
					25
				');
			$stm->bindInteger($this->User->getId());
			$this->result = $stm->getRowSet();
			}
		else
			{
			$this->result = $this->DB->getRowSet
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
					(SELECT text FROM posts WHERE threadid = threads.id AND dat = threads.firstdate) AS summary
				FROM
					forums,
					threads
				WHERE
					threads.forumid = forums.id
					AND threads.deleted = 0
				ORDER BY
					threads.lastdate DESC
				LIMIT
					25
				');
			}
		}
	catch (DBNoDataException $e)
		{
		$this->result = array();
		}

	$threads = $this->listThreads();

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