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

class PrivatePostings extends Postings {


public function prepare(){

if (!$this->User->isOnline())
	{
	$this->showWarning($this->L10n->getText('Access denied!'));
	}

try
	{
	$this->thread = $this->Input->Get->getInt('thread');
	}
catch (RequestException $e)
	{
	$this->showWarning($this->L10n->getText('No topic specified.'));
	}


$this->post = $this->Input->Get->getInt('post', 0);

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
	$this->showWarning($this->L10n->getText('Topic not found.'));
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
		$users[] = '<a href="'.$this->Output->createUrl('ShowUser', array('user' => $recipient['id'])).'">'.$recipient['name'].'</a>';
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


$first = true;
$postings = '';



foreach ($result as $data)
	{
	$postMenu = array();
	$modMenu = array();

	if ($data['dat'] > $lastVisit)
		{
		$status = 'status-new';
		}
	else
		{
		$status = 'status-old';
		}


	if ($data['editdate'] > 0)
		{
		if (empty($data['editorname']))
			{
			$edited = '<div class="posts-lastedit">'.$this->L10n->getText('Last edited').' ('.$this->L10n->getDateTime($data['editdate']).')</div>';
			}
		else
			{
			$edited = '<div class="posts-lastedit">'.sprintf($this->L10n->getText('Last edited by %s'), '<a href="'.$this->Output->createUrl('ShowUser', array('user' => $data['editby'])).'">'.$data['editorname'].'</a> ('.$this->L10n->getDateTime($data['editdate'])).')</div>';
			}
		}
	else
		{
		$edited = '';
		}

	if ($this->User->isUser($data['userid']))
		{
		if ($first && $this->post == 0)
			{
			$modMenu[] = '<a href="'.$this->Output->createUrl('EditPrivateThread', array('thread' => $this->thread)).'">'.$this->L10n->getText('Edit topic').'</a>';
			$first = false;
			}
		else
			{
			$modMenu[] = '<a href="'.$this->Output->createUrl('EditPrivatePost', array('post' => $data['id'])).'">'.$this->L10n->getText('Edit post').'</a>';
			}
		}

	$postMenu[] = '<a href="'.$this->Output->createUrl('QuotePrivatePost', array('post' =>  $data['id'])).'">'.$this->L10n->getText('Quote post').'</a>';

	if (count($modMenu) > 1)
		{
		$postMenu[] = 'Moderation<ul><li>'.implode('</li><li>', $modMenu).'</li></ul>';
		}
	elseif (count($modMenu) == 1)
		{
		$postMenu[] = array_pop($modMenu);
		}

	$poster = (!empty($data['userid']) ? '<a href="'.$this->Output->createUrl('ShowUser', array('user' => $data['userid'])).'">'.$data['name'].'</a>' : $data['username']);

	$avatar = (empty($data['avatar']) ? '' : '<img src="'.$this->Output->createUrl('GetAvatar', array('user' => $data['userid'])).'" alt="" />');

	$files = $data['file'] == 1 ? $this->getFiles($data['id']) : '';

	$postings .=
		'
		<tbody class="'.$status.'">
			<tr>
				<td class="posts-user">
					'.$poster.'
				</td>
				<td class="posts-date">
					'.$this->L10n->getDateTime($data['dat']).'
				</td>
			</tr>
			<tr>
				<td rowspan="2" class="posts-avatar">
					'.$avatar.'
				</td>
				<td class="posts-text">
					'.$data['text'].$files.$edited.'
				</td>
			</tr>
			<tr>
				<td class="posts-menu">
					<ul>
						<li>'.implode('</li><li>', $postMenu).'</li>
					</ul>
				</td>
			</tr>
		</tbody>
		';
		}
$stm->close();

$poll = $thread['poll'] == 1 ? $this->getPoll() : '';

$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('InviteToPrivateThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Invite to private thread').'</a>');
$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('NewPrivatePost', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Post reply').'</a>');

if ($thread['firstuserid'] == $this->User->getId())
	{
	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('DelPrivateThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Delete topic').'</a>');
	}


$body =
	'
	<table id="posts">
		<thead>
			<tr>
				<th colspan="2">
					'.$pages.'
				</th>
			</tr>
			<tr>
				<th>Empfänger</th>
				<td>
					'.$recipients.'
				</td>
			</tr>
		</thead>
		'.$poll.'
		'.$postings.'
		<tfoot>
			<tr>
				<th colspan="2">
					'.$pages.'
				</th>
			</tr>
		</tfoot>
	</table>
	';
$this->setTitle($thread['name']);
$this->setBody($body);
}


}
?>