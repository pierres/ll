<?php


class ShowBugs extends Search{


private $forum = 290;


public function prepare()
	{
	$this->setValue('title', 'Offene Bugs');

	try
		{
		$this->result = $this->Sql->fetch
			('
			SELECT
				id,
				name,
				lastdate,
				posts,
				lastuserid,
				lastusername,
				firstdate,
				firstuserid,
				firstusername,
				closed,
				sticky,
				poll,
				posts,
				'.$this->forum.' AS forumid,
				\'BugReport\' AS forumname
			FROM
				threads
			WHERE
				forumid = '.$this->forum.'
				AND closed = 0
				AND deleted = 0
			ORDER BY
				firstdate DESC
			');
		}
	catch (SqlNoDataException $e)
		{
		$this->result = array();
		}

	try
		{
		$closed = $this->Sql->numRows
			('
				threads
			WHERE
				forumid = '.$this->forum.'
				AND closed = 1
				AND deleted = 0
			');
		}
	catch (SqlNoDataException $e)
		{
		$closed = 0;
		}

	try
		{
		$opend = $this->Sql->numRows
			('
				threads
			WHERE
				forumid = '.$this->forum.'
				AND closed = 0
				AND deleted = 0
			');
		}
	catch (SqlNoDataException $e)
		{
		$opend = 0;
		}

	$body =
		'
		<table class="frame" style="width:100%">
			<tr>
				<td class="title" colspan="2">Statistik</td>
			</tr>
			<tr>
				<td class="main">Bugs offen</td>
				<td class="main" style="width:100%">'.$opend.'</td>
			</tr>
			<tr>
				<td class="main">Bugs&nbsp;geschlossen</td>
				<td class="main" style="width:100%">'.$closed.'</td>
			</tr>
		</table>
		<br />
		<table class="frame" style="width:100%">
			<tr>
				<td class="title" colspan="2">Thema</td>
				<td class="title">Erster Beitrag</td>
				<td class="title">Beiträge</td>
				<td class="title">Letzter Beitrag</td>
				<td class="title">Forum</td>
			</tr>
			'.$this->listThreads().'
		</table>
		';

	$this->setValue('body', $body);
	}

}


?>