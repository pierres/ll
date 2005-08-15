<?php



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
			$this->forum = $this->Sql->fetchValue
				('
				SELECT
					id
				FROM
					forums
				WHERE
					boardid = '.$this->Board->getId().'
				ORDER BY
					id ASC
				');
			}
		catch(SqlNoDataException $e)
			{
			$this->Io->redirect('Forums');
			}
		}

	$this->setValue('title', 'Portal');

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


	$this->setValue('body', $body);
	}

private function getNews()
	{
	try
		{
		$threads = $this->Sql->fetch
			('
			SELECT
				threads.id,
				threads.name,
				threads.firstdate,
				threads.firstusername,
				threads.firstuserid,
				posts.text
			FROM
				threads
					LEFT JOIN posts
					ON posts.threadid = threads.id AND posts.dat = threads.firstdate
			WHERE
				threads.forumid = '.$this->forum.'
				AND threads.forumid != 0
				AND threads.deleted = 0
			ORDER BY
				threads.id DESC
			LIMIT
				10
			');
		}
	catch(SqlNoDataException $e)
		{
		$threads = array();
		}

	$result = '';

	foreach ($threads as $thread)
		{
		if ($this->User->isOnline() && $this->Log->isNew($thread['id'], $thread['firstdate']))
			{
			$thread['name'] = '<span class="newthread">'.$thread['name'].'</span>';
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
						<div>'.preg_replace('#^(.+?)<br /><br /><br />.*#', '$1', $thread['text']).'</div>
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

	return $result;
	}

private function getRecent()
	{
	try
		{
		if ($this->User->isOnline())
			{
			$threads = $this->Sql->fetch
				('
				(
					SELECT
						threads.id,
						threads.name,
						threads.lastdate,
						threads.forumid
					FROM
						threads,
						thread_user
					WHERE
						threads.forumid = 0
						AND threads.deleted = 0
						AND thread_user.threadid = threads.id
						AND thread_user.userid = '.$this->User->getId().'
				)
				UNION
				(
					SELECT
						id,
						name,
						lastdate,
						forumid
					FROM
						threads
					WHERE
						deleted = 0
						AND forumid != 0
						AND forumid != '.$this->forum.'
				)
				ORDER BY
					lastdate DESC
				LIMIT
					25
				');
			}
		else
			{
			$threads = $this->Sql->fetch
				('
				SELECT
					id,
					name,
					lastdate,
					forumid
				FROM
					threads
				WHERE
					deleted = 0
					AND forumid != 0
					AND forumid != '.$this->forum.'
				ORDER BY
					lastdate DESC
				LIMIT
					25
				');
			}
		}
	catch(SqlNoDataException $e)
		{
		$threads = array();
		}

	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	foreach ($threads as $thread)
		{
		$thread['name'] = cutString($thread['name'], 28);

		if ($this->User->isOnline() && $this->Log->isNew($thread['id'], $thread['lastdate']))
			{
			$thread['name'] = '<span class="newthread">'.$thread['name'].'</span>';
			}

		if ($thread['forumid'] == 0)
			{
			$result .= '<li style="margin-bottom:5px;"><a href="?page=PrivatePostings;thread='.$thread['id'].';post=-1;id='.$this->Board->getId().'">'.$thread['name'].'</a></li>';
			}
		else
			{
			$result .= '<li style="margin-bottom:5px;"><a href="?page=Postings;thread='.$thread['id'].';post=-1;id='.$this->Board->getId().'">'.$thread['name'].'</a></li>';
			}
		}

	return $result.'</ul>';
	}

private function getBoards()
	{
	try
		{
		$boards = $this->Sql->fetch
			('
			SELECT
				id,
				name
			FROM
				boards
			');
		}
	catch(SqlNoDataException $e)
		{
		$boards = array();
		}

	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	foreach ($boards as $board)
		{
		$result .= '<li style="margin-bottom:5px;"><a href="?page=Forums;id='.$board['id'].'">'.cutString($board['name'], 25).'</a></li>';
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
	return $this->Sql->fetchValue('SELECT description FROM boards WHERE id = '.$this->Board->getId());
	}

private function getActiveMembers()
	{
	try
		{
		$users = $this->Sql->fetch
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
		}
	catch(SqlNoDataException $e)
		{
		$users = array();
		}

	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	foreach ($users as $user)
		{
		$result .= '<li style="margin-bottom:5px;"><a href="?page=ShowUser;user='.$user['id'].';id='.$this->Board->getId().'">'.$user['name'].'</a></li>';
		}

	return $result.'</ul>';
	}

private function getNewMembers()
	{
	try
		{
		$users = $this->Sql->fetch
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
		}
	catch(SqlNoDataException $e)
		{
		$users = array();
		}

	$result = '<ul style="list-style:square;text-align:left;margin:0px;padding-left:12px;">';

	foreach ($users as $user)
		{
		$result .= '<li style="margin-bottom:5px;"><a href="?page=ShowUser;user='.$user['id'].';id='.$this->Board->getId().'">'.$user['name'].'</a></li>';
		}

	return $result.'</ul>';
	}

}



?>