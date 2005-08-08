<?php


class Recent extends Search{


public function prepare()
	{
	$this->setValue('title', 'Aktuelles');

	try
		{
		$this->result = $this->Sql->fetch
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
				forums.name AS forumname
			FROM
				forums,
				threads
			WHERE
				threads.forumid = forums.id
				AND threads.deleted = 0
				AND threads.lastdate > '.(time()-(86400 * Settings::SESSION_TIMEOUT)).'
			ORDER BY
				threads.lastdate DESC
			LIMIT
				25
			');
		}
	catch (SqlNoDataException $e)
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