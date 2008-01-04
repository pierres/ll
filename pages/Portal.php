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
class Portal extends Page{

private $forum;

public function prepare()
	{
	try
		{
		$this->forum = $this->Io->getInt('forum');
		}
	catch (IoRequestException $e)
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					id
				FROM
					forums
				WHERE
					boardid = ?
				ORDER BY
					id ASC
				');
			$stm->bindInteger($this->Board->getId());
			$this->forum = $stm->getColumn();
			$stm->close();
			}
		catch(DBNoDataException $e)
			{
			$stm->close();
			$this->Io->redirect('Forums');
			}
		}

	$this->setValue('title', 'Portal');

	if (!($body = $this->ObjectCache->getObject('LL:Portal::'.$this->Board->getId())))
		{
		$body =
		'
		<table>
			<tr>
				<td style="vertical-align:top;width:200px;">
					<table class="frame" style="width:100%;">
						<tr>
							<td class="title">
								Mitglieder-Foren
							</td>
						</tr>
						<tr>
							<td class="post0">
							'.$this->getBoards().'
							</td>
						</tr>
					</table>
					<table class="frame" style="margin-top:12px;width:100%;">
						<tr>
							<td class="title">
								Wer ist noch hier?
							</td>
						</tr>
						<tr>
							<td class="post0">
							'.$this->getOnline().'
							</td>
						</tr>
					</table>
					<table class="frame" style="margin-top:12px;width:100%;">
						<tr>
							<td class="title">
								Aktive Mitglieder
							</td>
						</tr>
						<tr>
							<td class="post0">
							'.$this->getActiveMembers().'
							</td>
						</tr>
					</table>
				</td>
				<td style="vertical-align:top;padding-left:12px;padding-right:12px;">
					<table class="frame" style="margin-bottom:20px;width:100%;">
						<tr>
							<td class="title">
								Willkommen bei '.$this->Board->getName().'
							</td>
						</tr>
						<tr>
							<td class="post1">
							'.$this->getDescription().'
							</td>
						</tr>
					</table>
					'.$this->getNews().'
					<div>
						<a href="?page=Threads;forum='.$this->forum.';id='.$this->Board->getId().'"><span class="button">mehr Neuigkeiten</span></a>
					</div>
				</td>
				<td style="vertical-align:top;width:250px;">
					<table class="frame" style="width:100%;">
						<tr>
							<td class="title">
								Aktuelle Themen
							</td>
						</tr>
						<tr>
							<td class="post0">
							'.$this->getRecent().'
							</td>
						</tr>
					</table>
					<table class="frame" style="margin-top:12px;width:100%;">
						<tr>
							<td class="title">
								Neue Mitglieder
							</td>
						</tr>
						<tr>
							<td class="post0">
							'.$this->getNewMembers().'
							</td>
						</tr>
					</table>
				</td>
			</tr>
		</table>
		';

		$this->ObjectCache->addObject('LL:Portal::'.$this->Board->getId(), $body, 60*60);
		}

	$this->setValue('body', $body);
	}

private function getNews()
	{
	$result = '';

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				firstdate,
				firstusername,
				firstuserid,
				summary
			FROM
				threads
			WHERE
				forumid = ?
				AND forumid != 0
				AND deleted = 0
			ORDER BY
				id DESC
			LIMIT
				10
			');
		$stm->bindInteger($this->forum);

		foreach ($stm->getRowSet() as $thread)
			{
			if ($this->User->isOnline() && $this->Log->isNew($thread['id'], $thread['firstdate']))
				{
				$thread['name'] = '<span class="newthread">neu</span>'.$thread['name'];
				}

			$result .=
				'
				<table class="frame" style="width:100%;margin-bottom:12px;">
					<tr>
						<td class="title" style="text-align:left">
							'.$thread['name'].'
						</td>
					</tr>
					<tr>
						<td class="post0">
							<div class="postdate" style="margin-bottom:5px;">'.formatDate($thread['firstdate']).'</div>
							<div>'.$thread['summary'].'</div>
							<div class="postdate" style="margin-top:5px;">
							'.(empty($thread['firstuserid']) ? $thread['firstusername'] : '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$thread['firstuserid'].'">'.$thread['firstusername'].'</a>').'
							</div>
						</td>
					</tr>
					<tr>
						<td class="post0">
						<div class="postbuttons">
						<a href="?page=Postings;thread='.$thread['id'].';id='.$this->Board->getId().'"><span class="button">lesen</span></a>
						<a href="?page=NewPost;thread='.$thread['id'].';id='.$this->Board->getId().'"><span class="button">antworten</span></a>
						</div>
						</td>
					</tr>
				</table>
				';
			}
		$stm->close();
			}
	catch(DBNoDataException $e)
		{
		$stm->close();
		}

	return $result;
	}

private function getRecent()
	{
	/** TODO Potentiell teure Anfrage */
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.forumid
			FROM
				forums,
				threads,
				forum_cat,
				cats
			WHERE
				threads.deleted = 0
				AND threads.forumid != ?
				AND threads.forumid = forums.id
				AND forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND cats.boardid = ?
			ORDER BY
				threads.lastdate DESC
			LIMIT
				25
			');

		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->Board->getId());
		$threads = $stm->getRowSet();
		}
	catch(DBNoDataException $e)
		{
		$threads = array();
		}

	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	foreach ($threads as $thread)
		{
		$thread['name'] = cutString($thread['name'], 28);

		if ($this->User->isOnline() && $this->Log->isNew($thread['id'], $thread['lastdate']))
			{
			$thread['name'] = '<span class="newthread">neu</span>'.$thread['name'];
			}

			$result .= '<li style="margin-bottom:5px;"><a href="?page=Postings;thread='.$thread['id'].';post=-1;id='.$this->Board->getId().'">'.$thread['name'].'</a></li>';
		}
	$stm->close();

	return $result.'</ul>';
	}

private function getBoards()
	{
	try
		{
		$boards = $this->DB->getRowSet
			('
			SELECT
				host,
				name
			FROM
				boards
			');
		}
	catch(DBNoDataException $e)
		{
		$boards = array();
		}

	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	foreach ($boards as $board)
		{
		$result .= '<li style="margin-bottom:5px;"><a href="http://'.$board['host'].'">'.cutString($board['name'], 25).'</a></li>';
		}

	return $result.'</ul>';
	}

private function getOnline()
	{
	$users = $this->User->getOnline();

	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	foreach ($users as $user)
		{
		$result .= '<li style="margin-bottom:5px;"><a href="?page=ShowUser;user='.$user['id'].';id='.$this->Board->getId().'">'.$user['name'].'</a></li>';
		}

	return $result.'</ul>';
	}

private function getDescription()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				description
			FROM
				boards
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Board->getId());
		return $stm->getColumn();
		}
	catch(DBNoDataException $e)
		{
		return '';
		}
	}

private function getActiveMembers()
	{
	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	try
		{
		$users = $this->DB->getRowSet
			('
			SELECT
				id,
				name
			FROM
				users
			ORDER BY
				lastpost DESC
			LIMIT
				25
			');

		foreach ($users as $user)
			{
			$result .= '<li style="margin-bottom:5px;"><a href="?page=ShowUser;user='.$user['id'].';id='.$this->Board->getId().'">'.$user['name'].'</a></li>';
			}
		}
	catch(DBNoDataException $e)
		{
		}

	return $result.'</ul>';
	}

private function getNewMembers()
	{
	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	try
		{
		$users = $this->DB->getRowSet
			('
			SELECT
				id,
				name
			FROM
				users
			ORDER BY
				id DESC
			LIMIT
				25
			');

		foreach ($users as $user)
			{
			$result .= '<li style="margin-bottom:5px;"><a href="?page=ShowUser;user='.$user['id'].';id='.$this->Board->getId().'">'.$user['name'].'</a></li>';
			}
		}
	catch(DBNoDataException $e)
		{
		}

	return $result.'</ul>';
	}

}



?>