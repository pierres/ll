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
require('Postings.php');

class PrivatePostings extends Postings{


public function prepare(){

	if (!$this->User->isOnline())
		{
		$this->showWarning('Nur für Mitglieder');
		}

try
	{
	$this->thread = $this->Input->Request->getInt('thread');
	}
catch (RequestException $e)
	{
	$this->showWarning('Kein Thema angegeben');
	}

try
	{
	$this->post = $this->Input->Request->getInt('post');
	}
catch (RequestException $e)
	{
	$this->post = 0;
	}

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			threads.name,
			threads.poll,
			threads.id,
			threads.lastdate,
			threads.firstuserid
		FROM
			threads,
			thread_user
		WHERE
			threads.id = ?
			AND threads.forumid = 0
			AND thread_user.threadid = threads.id
			AND thread_user.userid = ?
			AND
				((thread_user.userid = threads.firstuserid
				AND threads.deleted = 1)
				OR threads.deleted = 0)'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->User->getId());
	$thread = $stm->getRow();
	$stm->close();
	}
catch (DBNoDataException $e)
	{
	$stm->close();
	$this->Output->setStatus(Output::NOT_FOUND);
	$this->showWarning('Thema nicht gefunden.');
	}

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			posts
		WHERE
			posts.threadid = ?'
		);
	$stm->bindInteger($this->thread);
	$this->posts = $stm->getColumn();
	$stm->close();
	}
catch (DBNoDataException $e)
	{
	$stm->close();
	$this->posts = 0;
	}

$lastVisit = $this->Log->getTime($this->thread);

if ($this->post < 0)
	{
	if ($this->Log->isNew($this->thread, $thread['lastdate']))
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					COUNT(*)
				FROM
					posts
				WHERE
					posts.threadid = ?
					AND dat >= ?
				');
			$stm->bindInteger($this->thread);
			$stm->bindInteger($lastVisit);
			$this->post = $this->posts - $stm->getColumn()-1;
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			$this->post = $this->posts-1;
			}
		}
	else
		{
		$this->post = nat($this->posts- $this->Settings->getValue('max_posts'));
		}
	}


$pages = $this->getPages();


$this->Log->insert($thread['id'], $thread['lastdate']);

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			users.id,
			users.name
		FROM
			users,
			thread_user
		WHERE
			thread_user.threadid = ?
			AND thread_user.userid = users.id
			AND users.id <> ?
		');
	$stm->bindInteger($thread['id']);
	$stm->bindInteger($this->User->getId());

	$users = array();
	foreach ($stm->getRowSet() as $recipient)
		{
		$users[] = '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$recipient['id'].'">'.$recipient['name'].'</a>';
		}
	$stm->close();

	$recipients = implode(', ', $users);
	}
catch (DBNoDataException $e)
	{
	$stm->close();
	$recipients = '';
	}

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			posts.id,
			posts.userid,
			posts.username,
			users.name,
			users.avatar,
			posts.dat,
			posts.editdate,
			posts.editby,
			posts.file,
			editors.name AS editorname,
			posts.text
		FROM
			posts
				LEFT JOIN users
					ON posts.userid = users.id
				LEFT JOIN users AS editors
					ON posts.editby = editors.id
		WHERE
			posts.threadid = ?
			AND posts.counter BETWEEN ? AND ?
		ORDER BY
			posts.dat ASC
		');
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->post);
	$stm->bindInteger($this->post+$this->Settings->getValue('max_posts')-1);
	$result = $stm->getRowSet();
	}
catch (DBNoDataException $e)
	{
	$result = array();
	}


$postings 	= '';
$i 		= 2;
$first 		= true;


foreach ($result as $data)
	{
	$i = abs($i-1);
	$style = 'class="post'.$i.'"';

	$postid = $data['id'];

	if ($data['dat'] > $lastVisit)
		{
		$data['dat'] = '<span class="newthread">neu</span>'.formatDate($data['dat']);
		}
	else
		{
		$data['dat'] = formatDate($data['dat']);
		}

	if ($data['editdate'] > 0)
		{
		if (empty($data['editorname']))
			{
			$edited = '<div class="postedit">am '.formatDate($data['editdate']).' geändert</div>';
			}
		else
			{
			$edited = '<div class="postedit">von <a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['editby'].'">'.$data['editorname'].'</a> am '.formatDate($data['editdate']).' geändert</div>';
			}
		}
	else
		{
		$edited = '';
		}

	if ($first && $this->post == 0)
		{
		$edit_button = (($this->User->isUser($data['userid'])) ?
					' <a href="?page=EditPrivateThread;id='.$this->Board->getId().';thread='.$this->thread.'"><span class="button">Thema ändern</span></a>' : '');
		$first = false;
		}
	else
		{
		$edit_button = (($this->User->isUser($data['userid'])) ?
					' <a href="?page=EditPrivatePost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">ändern</span></a>' : '');
		}

	$quote_button = '<a href="?page=QuotePrivatePost;id='.$this->Board->getId().';post='.$postid.'"><span class="button">zitieren</span></a>';


	$poster = (!empty($data['userid']) ? '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['userid'].'">'.$data['name'].'</a>' : $data['username']);

	$avatar = (empty($data['avatar']) ? '' : '<img src="?page=GetAvatar;user='.$data['userid'].'" class="avatar" />');

	if ($data['file'] == 1)
		{
		$files = $this->getFiles($data['id']);
		}
	else
		{
		$files = '';
		}

	$postings .=
		'
		<tr>
			<td '.$style.' rowspan="2" style="vertical-align:top;width:150px;">
				<div class="postname">'.$poster.'</div>
			</td>
			<td '.$style.' style="vertical-align:top;">
				<div class="postdate">'.$data['dat'].'</div>
			</td>
			<td '.$style.'>
				<div class="postbuttons">'.$quote_button.$edit_button.'</div>
			</td>
		</tr>
		<tr>
			<td '.$style.' rowspan="2" colspan="2">
				'.$data['text'].$files.'
			</td>
		</tr>
		<tr>
			<td  '.$style.' rowspan="2" style="vertical-align:top;text-align:center;">
				'.$avatar.'
			</td>
		</tr>
		<tr>
			<td '.$style.' colspan="2">
				'.$edited.'
			</td>
		</tr>
		';
		}
$stm->close();

if ($thread['poll'] == 1)
	{
	/** Poll sollte extra schließbar sein */
	$Poll = new Poll($thread['id'], 'PrivatePostings');
	$poll = $Poll->showPoll();
	}
else
	{
	$poll = '';
	}


$reply_button = '<a href="?page=InviteToPrivateThread;id='.$this->Board->getId().';thread='.$thread['id'].'"><span class="button">einladen</span></a>
<a href="?page=NewPrivatePost;id='.$this->Board->getId().';thread='.$thread['id'].'"><span class="button">antworten</span></a>';


$thread_buttons = ($thread['firstuserid'] == $this->User->getId() ?
	'<tr><td class="pages" colspan="3"><a href="?page=DelPrivateThread;id='.$this->Board->getId().';thread='.$thread['id'].'"><span class="button">Privates Thema löschen</span></a></td></tr>' : '');

$body =
	'
	<table class="frame" style="width:100%">
		'.$poll.'
		<tr>
			<td class="title" colspan="3">
				'.$thread['name'].'
			</td>
		</tr>
		<tr>
			<td class="path" colspan="3">
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'">'.$this->Board->getName().'</a>
				&#187;
				<a class="pathlink" href="?page=PrivateThreads;id='.$this->Board->getId().'">Private Themen</a>
			</td>
		</tr>
		<tr>
			<td class="pages">
				'.$pages.'&nbsp;
			</td>
			<td class="pages">
			Schon dabei: '.$recipients.'
			</td>
			<td class="pages" style="text-align:right">
				'.$reply_button.'
			</td>
		</tr>
			'.$postings.'
		<tr>
			<td class="pages">
				'.$pages.'&nbsp;
			</td>
			<td class="pages" colspan="2" style="text-align:right">
				'.$reply_button.'
				<a id="last"></a>
			</td>
		</tr>
		<tr>
			<td class="path" colspan="3">
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'">'.$this->Board->getName().'</a>
				&#187;
				<a class="pathlink" href="?page=PrivateThreads;id='.$this->Board->getId().'">Private Themen</a>
			</td>
		</tr>
		'.$thread_buttons.'
	</table>
	';
$this->setValue('title', $thread['name']);
$this->setValue('body', $body);
}


}
?>