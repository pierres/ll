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

class Forums extends Page {


public function prepare() {


if ($this->User->isOnline())
	{
	$forums = $this->getPrivateThreads();
	$catcount = 2;
	$forumcount = 2;
	}
else
	{
	$catcount = 1;
	$forumcount = 1;
	$forums = '';
	}

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			cats.id AS catid,
			cats.name AS catname,
			forums.id,
			forums.boardid,
			forums.name,
			forums.description,
			forums.lastthread,
			forums.threads,
			forums.posts,
			threads.lastusername,
			threads.lastdate,
			threads.name AS threadname
		FROM
			cats,
			forums
				LEFT JOIN threads
				ON forums.lastthread = threads.id,
			forum_cat
		WHERE
			cats.boardid = ?
			AND forum_cat.forumid = forums.id
			AND forum_cat.catid = cats.id
		ORDER BY
			cats.position,
			forum_cat.position
		');
	$stm->bindInteger($this->Board->getId());
	$result = $stm->getRowSet();
	}
catch (DBNoDataException $e)
	{
	$result = array();
	}

$cat 		= 0;
$catheader 	= '';


foreach ($result as $data)
	{
	$catheader = '';

	if ($cat != $data['catid'])
		{
		if ($catcount > 1)
			{
			$catheader .= '</div>';
			}

		$catheader .=
			'
			<div class="main-subhead">
				<h2 class="hn"><span>'.$data['catname'].'</span></h2>
				<p class="item-summary"><span><strong class="info-topics">'.$this->L10n->getText('topics').'</strong> <strong class="info-posts">'.$this->L10n->getText('posts').'</strong> <strong class="info-lastpost">'.$this->L10n->getText('last post').'</strong></span></p>
	
			</div>
			<div id="category'.$catcount.'" class="main-content main-category">
			';

		$item1 = ' main-item1';

		$catcount++;
		}
	else
		{
		$item1 = '';
		}

	if ($this->User->isOnline() && $this->Log->isNew($data['lastthread'], $data['lastdate']))
		{
		$status = ' new';
		$icon = '<a href="'.$this->Output->createUrl('MarkAsRead', array('forum' => $data['id'])).'"><span class="icon '.$status.'"><!-- --></span></a>';
		}
	else
		{
		$status = '';
		$icon = '<span class="icon"><!-- --></span>';
		}

	$position = ($forumcount % 2 == 0 ? 'even' : 'odd');

	$data['lastdate'] = $this->L10n->getDateTime($data['lastdate']);

	$lastposter = empty($data['lastusername']) ? '' : $this->L10n->getText('by').' '.$data['lastusername'];

	$forums .= $catheader.
		'
		<div id="forum'.$forumcount.'" class="main-item '.$position.$status.$item1.'">
			'.$icon.'
			<div class="item-subject">
				<h3 class="hn"><a href="'.$this->Output->createUrl('Threads', array('forum' => $data['id'])).'"><span>'.$data['name'].'</span></a></h3>
				<p>'.$data['description'].'</p>
			</div>
			<ul class="item-info">
				<li class="info-topics"><strong>'.$this->L10n->getNumber($data['threads']).'</strong></li>
				<li class="info-posts"><strong>'.$this->L10n->getNumber($data['posts']).'</strong></li>
				<li class="info-lastpost"><strong><a href="'.$this->Output->createUrl('Postings', array('thread' => $data['lastthread'], 'post' => '-1')).'">'.$data['lastdate'].'</a></strong> <cite>'.$lastposter.'</cite></li>
			</ul>
		</div>
		';

	$forumcount++;

	$cat = $data['catid'];
	}
$stm->close();

if ($forumcount > 1) 
	{
	$forums .= '</div>';
	}

$body =	'<div class="main-head">
		<h1 class="hn"><span>'.$this->Board->getName().'</span></h1>
	</div>
	'.$forums.'
	<div class="main-options gen-content">
		<p class="options">'.($this->User->isOnline() ? '<span class="item1"><a href="'.$this->Output->createUrl('MarkAllAsRead').'">'.$this->L10n->getText('Mark all topics as read').'</a></span>' : '&nbsp;').'</p>
	</div>
	';

$this->setTitle($this->L10n->getText('Index'));
$this->setBody($body);

$this->setValue('stats', $this->getBoardStatistics());
}

private function getBoardStatistics()
	{
	$online = array();
	foreach($this->User->getOnline() as $user)
		{
		$online[] = '<a href="'.$this->Output->createUrl('ShowUser', array('user' => $user['id'])).'">'.$user['name'].'</a>';
		}
	$online = implode(', ', $online);

	$stats =
	'<div id="brd-stats" class="gen-content">
		<ul>
			<li class="st-activity"><span>'.$this->L10n->getText('Total number of topics').':</span> <strong>'.$this->L10n->getNumber($this->Board->getThreads()).'</strong></li>
			<li class="st-activity"><span>'.$this->L10n->getText('Total number of posts').':</span> <strong>'.$this->L10n->getNumber($this->Board->getPosts()).'</strong></li>
		</ul>
	</div>
	<div id="brd-online" class="gen-content">
		<h3 class="hn"><span>'.$this->L10n->getText('Currently online').' ( <strong>'.$this->L10n->getNumber($this->User->getOnlineCount()).'</strong> )</span></h3>
		<p>'.$online.'</p>
	</div>';

	return $stats;
	}

private function getPrivateThreads()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id AS lastthread,
				threads.lastusername,
				threads.lastdate,
				threads.name AS threadname
			FROM
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = ?
			ORDER BY
				threads.lastdate DESC
			');
		$stm->bindInteger($this->User->getId());
		$data = $stm->getRow();
		$stm->close();

		$stm = $this->DB->prepare
			('
			SELECT
				COUNT(*) AS threads,
				SUM(posts) AS posts
			FROM
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = ?'
			);
		$stm->bindInteger($this->User->getId());
		$count = $stm->getRow();
		$stm->close();

		$data['posts'] = $count['posts'];
		$data['threads'] = $count['threads'];
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$data['lastthread'] = '';
		$data['lastusername'] = '';
		$data['posts'] = 0;
		$data['lastdate'] = 0;
		$data['threads'] = 0;
		$data['threadname'] = '';
		}

	if ($this->Log->isNew($data['lastthread'], $data['lastdate']))
		{
		$status = ' new';
		}
	else
		{
		$status = '';
		}
		
	$icon = '<span class="icon '.$status.'"><!-- --></span>';

	$lastposter = empty($data['lastusername']) ? '' : $this->L10n->getText('by').' '.$data['lastusername'];
	
	$data['lastdate'] = $this->L10n->getDateTime($data['lastdate']);

	return
		'
		<div class="main-subhead">
			<h2 class="hn"><span>'.$this->L10n->getText('Private topics').'</span></h2>
			<p class="item-summary"><span><strong class="info-topics">'.$this->L10n->getText('topics').'</strong> <strong class="info-posts">'.$this->L10n->getText('posts').'</strong> <strong class="info-lastpost">'.$this->L10n->getText('last post').'</strong></span></p>

		</div>
		<div id="category1" class="main-content main-category">

		<div id="forum1" class="main-item odd'.$status.' main-item1">
			'.$icon.'
			<div class="item-subject">
				<h3 class="hn"><a href="'.$this->Output->createUrl('PrivateThreads').'"><span>'.$this->L10n->getText('Private topics').'</span></a></h3>
				<p>'.$this->L10n->getText('Discuss with other members in a private area.').'</p>
			</div>
			<ul class="item-info">
				<li class="info-topics"><strong>'.$this->L10n->getNumber($data['threads']).'</strong></li>
				<li class="info-posts"><strong>'.$this->L10n->getNumber($data['posts']).'</strong></li>
				<li class="info-lastpost"><strong><a href="'.$this->Output->createUrl('Postings', array('thread' => $data['lastthread'], 'post' => '-1')).'">'.$data['lastdate'].'</a></strong> <cite>'.$lastposter.'</cite></li>
			</ul>
		</div>
		';
	}

}


?>
