<?php


class UserRecent extends Page{


public function prepare()
	{
	$this->setValue('title', 'Aktuelle Beiträge');
	$this->setValue('meta.robots', 'noindex,nofollow');

	try
		{
		$user = $this->Io->getInt('user');
		}
	catch (IoRequestException $e)
		{
		$this->Io->redirect('Recent');
		}

	try
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
				summary
			FROM
				forums 	JOIN threads ON threads.forumid = forums.id
					JOIN posts ON posts.threadid = threads.id AND  posts.userid = ?
			WHERE
				threads.deleted = 0
			GROUP BY
				threads.id
			ORDER BY
				threads.lastdate DESC
			LIMIT
				25
			');

		$stm->bindInteger($user);
		$result = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$result = array();
		}

	$threads = $this->ThreadList->getList($result);
	$stm->close();

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