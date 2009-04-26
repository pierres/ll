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


$postings 	= '';

$first 		= true;
$postcount	= 1;
$posttype 	= array('post');


foreach ($result as $data)
	{

	$postid = $data['id'];

	if ($data['dat'] > $lastVisit)
		{
		$posttype[] = 'newpost';
		}

	if ($data['editdate'] > 0)
		{
		if (empty($data['editorname']))
			{
			$edited = '<p class="lastedit"><em>'.$this->L10n->getText('Last edited').' ('.$this->L10n->getDateTime($data['editdate']).')</em></p>';
			}
		else
			{
			$edited = '<p class="lastedit"><em>'.sprintf($this->L10n->getText('Last edited by %s'), '<a href="'.$this->Output->createUrl('ShowUser', array('user' => $data['editby'])).'">'.$data['editorname'].'</a> ('.$this->L10n->getDateTime($data['editdate'])).')</em></p>';
			}
		}
	else
		{
		$edited = '';
		}

	if ($first && $this->post == 0)
		{
		$edit_button = (($this->User->isUser($data['userid'])) ?
					' <a href="'.$this->Output->createUrl('EditPrivateThread', array('thread' => $this->thread)).'"><span>'.$this->L10n->getText('Edit topic').'</span></a>' : '');
		$first = false;
		}
	else
		{
		$edit_button = (($this->User->isUser($data['userid'])) ?
					' <a href="'.$this->Output->createUrl('EditPrivatePost', array('post' => $data['id'])).'"><span>'.$this->L10n->getText('Edit post').'</span></a>' : '');
		}

	$quote_button = '<a href="'.$this->Output->createUrl('QuotePrivatePost', array('post' => $postid)).'"><span>'.$this->L10n->getText('Quote post').'</span></a>';


	$poster = (!empty($data['userid']) ? '<a href="'.$this->Output->createUrl('ShowUser', array('user' => $data['userid'])).'">'.$data['name'].'</a>' : $data['username']);

	$avatar = (empty($data['avatar']) ? '' : '<img src="'.$this->Output->createUrl('GetAvatar', array('user' => $data['userid'])).'" alt="" />');

	if ($data['file'] == 1)
		{
		$files = $this->getFiles($data['id']);
		}
	else
		{
		$files = '';
		}

	$posttype[] = ($postcount % 2 == 0 ? 'even' : 'odd');
	$posttype[] = ($postcount == 1 ? 'firstpost' : '');
	$posttype[] = ($postcount == 1 && $this->post == 0 ? 'topicpost' : 'replypost');

	$postings .=
		'
		<div class="'.implode(' ', $posttype).'">
			<div class="postmain">
				<div id="p3" class="posthead">
					<h3><a class="permalink" rel="bookmark" href="'.$this->Output->createUrl('PrivatePostings', array('thread' => $thread['id'], 'post' => ($this->post + $postcount - 1))).'"><strong>'.($this->post + $postcount).'</strong></a> <span>'.$this->L10n->getDateTime($data['dat']).'</span></h3>
				</div>
				<div class="postbody">
					<div class="user">
						<h4 class="user-ident">'.$avatar.'<strong class="username">'.$poster.'</strong></h4>
					</div>
					<div class="post-entry">
						<div class="entry-content">
							<p>'.$data['text'].'</p>
							'.$edited.'
							'.$files.'
						</div>
					</div>
				</div>
				<div class="postfoot">
					<div class="post-options">
						'.$edit_button.$quote_button.'
					</div>
				</div>
			</div>
		</div>
		';

		$postcount++;
		$posttype = array('post');
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


$reply_button = '<a href="'.$this->Output->createUrl('InviteToPrivateThread', array('thread' => $thread['id'])).'"><span>'.$this->L10n->getText('Invite to private thread').'</span></a>
<a class="newpost" href="'.$this->Output->createUrl('NewPrivatePost', array('thread' => $thread['id'])).'"><span>'.$this->L10n->getText('Post reply').'</span></a>';

$thread_buttons = ($thread['firstuserid'] == $this->User->getId() ?
	'<a class="mod-option" href="'.$this->Output->createUrl('DelPrivateThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Delete topic').'</a>' : '');


$body =
	'
	<div id="brd-main" class="main paged">

	<h1><span><a class="permalink" href="'.$this->Output->createUrl('PrivatePostings', array('thread' => $thread['id'])).'" rel="bookmark">'.$thread['name'].'</a></span></h1>
'.$poll.'
	<div class="paged-head">
		<p class="paging"><span class="pages">'.$this->L10n->getText('Pages').':</span> '.$pages.'</p>
		<p class="posting">'.$reply_button.'</p>
	</div>

	<div class="main-head">
		<h2><span>'.$this->L10n->getText('Posts').' [ '.$this->posts.' ]</span></h2>
	</div>

	<div id="forum1" class="main-content topic">
		'.$postings.'
	</div>

	<div class="main-foot">
		<p class="h2"><strong>'.$this->L10n->getText('Posts').' [ '.$this->posts.' ]</strong></p>
		<p class="main-options">
			'.$thread_buttons.'
		</p>

	</div>

	<div class="paged-foot">
		<p class="posting">'.$reply_button.'</p>
		<p class="paging"><span class="pages">'.$this->L10n->getText('Pages').':</span> '.$pages.'</p>
	</div>

</div>
	';
$this->setTitle($thread['name']);
$this->setBody($body);
}


}
?>