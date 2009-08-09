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
	}
else
	{
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
			forums.name,
			forums.description,
			forums.lastthread,
			threads.lastusername,
			threads.lastdate
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
		$catheader .=
			'
			<tr>
				<th class="category" colspan="3">
					'.$data['catname'].'
				</th>
			</tr>
			';
		}

	if ($this->User->isOnline() && $this->Log->isNew($data['lastthread'], $data['lastdate']))
		{
		$icon = '<a href="'.$this->Output->createUrl('MarkAsRead', array('forum' => $data['id'])).'"><span class="status-new"></span></a>';
		}
	else
		{
		$icon = '<span class="status-old"></span>';
		}

	$data['lastdate'] = empty($data['lastdate']) ? '' : $this->L10n->getDateTime($data['lastdate']);

	$lastposter = empty($data['lastusername']) ? '' : $this->L10n->getText('by').' '.$data['lastusername'];

	$forums .= $catheader.
		'
		<tr>
			<th class="forum-status">
				'.$icon.'
			</th>
			<td class="forum-main">
				<div class="forum-title"><a href="'.$this->Output->createUrl('Threads', array('forum' => $data['id'])).'">'.$data['name'].'</a></div>
				<div class="forum-description">'.$data['description'].'</div>
			</td>
			<td class="forum-lastpost">
				<div><a href="'.$this->Output->createUrl('Postings', array('thread' => $data['lastthread'], 'post' => '-1')).'">'.$data['lastdate'].'</a></div>
				<div>'.$lastposter.'</div>
			</td>
		</tr>
		';

	$cat = $data['catid'];
	}
$stm->close();

$body =	'
	<table id="forum">
		<thead>
			<tr>
				<th colspan="2">
					'.$this->L10n->getText('Forum').'
				</th>
				<th>
					'.$this->L10n->getText('Last post').'
				</th>
			</tr>
		</thead>
		<tbody>
			'.$forums.'
		</tbody>
	</table>
	';

$this->setTitle($this->L10n->getText('Index'));
$this->setBody($body);
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
				threads.lastdate
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
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$data['lastthread'] = '';
		$data['lastusername'] = '';
		$data['lastdate'] = 0;
		}

	if ($this->Log->isNew($data['lastthread'], $data['lastdate']))
		{
		$status = ' new';
		}
	else
		{
		$status = 'old';
		}
		
	$icon = '<span class="status-'.$status.'"></span>';

	$data['lastdate'] = empty($data['lastdate']) ? '' : $this->L10n->getDateTime($data['lastdate']);
	$lastposter = empty($data['lastusername']) ? '' : $this->L10n->getText('by').' '.$data['lastusername'];

	return
		'
		<tr>
			<th class="forum-status">
				'.$icon.'
			</th>
			<td class="forum-main">
				<div class="forum-title"><a href="'.$this->Output->createUrl('PrivateThreads').'">'.$this->L10n->getText('Private topics').'</a></div>
				<div class="forum-description">'.$this->L10n->getText('Discuss with other members in a private area.').'</div>
			</td>
			<td class="forum-lastpost">
				<div><a href="'.$this->Output->createUrl('Postings', array('thread' => $data['lastthread'], 'post' => '-1')).'">'.$data['lastdate'].'</a></div>
				<div>'.$lastposter.'</div>
			</td>
		</tr>
		';
	}

}


?>
