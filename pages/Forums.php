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
class Forums extends Page{


public function prepare(){


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
			forums.boardid,
			forums.name,
			forums.description,
			forums.lastthread,
			forums.threads,
			forums.posts,
			threads.lastusername,
			threads.lastuserid,
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
	if ($cat != $data['catid'])
		{
		$catheader =
			'
			<tr>
				<td class="cat" colspan="5">
					<a class="catname" id="cat'.$data['catid'].'">&#171; '.$data['catname'].' &#187;</a>
				</td>
			</tr>
			';
		}
	else
		{
		$catheader = '';
		}

	if ($data['boardid'] == $this->Board->getId())
		{
		$new = '<span class="new"></span>';
		$old = '<span class="old"></span>';
		}
	else
		{
		$new = '<span class="newex"></span>';
		$old = '<span class="oldex"></span>';
		}

if ($this->User->isOnline())
	{
	$icon = ($this->Log->isNew($data['lastthread'], $data['lastdate']) ? '<a href="?page=MarkAsRead;id='.$this->Board->getId().';forum='.$data['id'].'">'.$new.'</a>' : $old);
	}
else
	{
	$icon = $old;
	}

	$data['lastdate'] = $this->L10n->getDateTime($data['lastdate']);

	$lastposter =
		(empty($data['lastuserid'])
		? (empty($data['lastusername']) ? '' : $this->L10n->getText('by').' '.$data['lastusername'])
		: $this->L10n->getText('by').' <a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['lastuserid'].'">'.$data['lastusername'].'</a>'
		);

	$forums .= $catheader.
			'
			<tr>
				<td class="iconcol">
					'.$icon.'
				</td>
				<td class="forumcol">
					<div class="forumtitle"><a href="?page=Threads;id='.$this->Board->getId().';forum='.$data['id'].'">'.$data['name'].'</a></div>
					<div class="forumdescr">'.$data['description'].'</div>
				</td>
				<td class="countcol">
					'.$this->L10n->getNumber($data['threads']).'
				</td>
				<td class="countcol">
					'.$this->L10n->getNumber($data['posts']).'
				</td>
				<td class="lastpost">
					<div><a href="?page=Postings;id='.$this->Board->getId().';thread='.$data['lastthread'].';post=-1">'.cutString($data['threadname'], 20).'</a></div>
					<div>'.$lastposter.'</div>
					<div>'.$data['lastdate'].'</div>
				</td>
			</tr>
		';

	$cat = $data['catid'];
	}
$stm->close();

$online = array();
foreach($this->User->getOnline() as $user)
	{
	$online[] = '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$user['id'].'">'.$user['name'].'</a>';
	}
$online = implode(', ', $online);

$body =
	'
	<table class="frame" style="width:100%">
		<tr>
			<td colspan="2" class="title">
				'.$this->L10n->getText('Forum').'
			</td>
			<td class="title">
				'.$this->L10n->getText('Topics').'
			</td>
			<td class="title">
				'.$this->L10n->getText('Posts').'
			</td>
			<td class="title">
				'.$this->L10n->getText('Last post').'
			</td>
		</tr>
		'.$forums.'
		<tr>
			<td colspan="5" class="cat">
				'.$this->L10n->getText('Who is online?').'
			</td>
		</tr>
		<tr>
			<td class="iconcol">
				&nbsp;
			</td>
			<td colspan="4" class="forumcol">
				<div class="forumdescr">'.$online.'</div>
			</td>
		</tr>
	</table>
	';

$this->setValue('title', $this->L10n->getText('Index'));
$this->setValue('body', $body);
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
				threads.lastuserid,
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
		$data['lastuserid'] = 0;
		$data['posts'] = 0;
		$data['lastdate'] = 0;
		$data['threads'] = 0;
		$data['threadname'] = '';
		}

	$icon = ($this->Log->isNew($data['lastthread'], $data['lastdate']) ? '<span class="new"></span>' : '<span class="old"></span>');

	$data['lastdate'] = $this->L10n->getDateTime($data['lastdate']);

	$lastposter =
		(empty($data['lastuserid'])
		? (empty($data['lastusername']) ? '' : $this->L10n->getText('by').' '.$data['lastusername'])
		: $this->L10n->getText('by').' <a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['lastuserid'].'">'.$data['lastusername'].'</a>'
		);

	return
		'
		<tr>
			<td class="iconcol">
				'.$icon.'
			</td>
			<td class="forumcol">
				<div class="forumtitle"><a href="?page=PrivateThreads;id='.$this->Board->getId().'">'.$this->L10n->getText('Private topics').'</a></div>
				<div class="forumdescr">'.$this->L10n->getText('Discuss with other members in a private area.').'</div>
			</td>
			<td class="countcol">
				'.$this->L10n->getNumber($data['threads']).'
			</td>
			<td class="countcol">
				'.$this->L10n->getNumber($data['posts']).'
			</td>
			<td class="lastpost">
				<div><a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$data['lastthread'].';post=-1">'.cutString($data['threadname'], 20).'</a></div>
				<div>'.$lastposter.'</div>
				<div>'.$data['lastdate'].'</div>
			</td>
		</tr>
		';
	}

}


?>