<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class Postings extends Page {

protected $ismod 		= false;
protected $thread		= 0;
protected $post 		= 0;
protected $posts 		= 0;

public function prepare(){

try
	{
	$this->thread = $this->Input->Get->getInt('thread');
	}
catch (RequestException $e)
	{
	$this->showWarning($this->L10n->getText('No topic specified'));
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
			threads.deleted,
			threads.closed,
			cats.name AS catname,
			cats.id AS catid,
			forums.name AS forumname,
			forums.id AS forumid,
			forums.mods
		FROM
			threads,
			forum_cat,
			forums,
			cats
		WHERE
			threads.id = ?
			AND threads.forumid = forum_cat.forumid
			AND forum_cat.catid = cats.id
			AND cats.boardid = ?
			AND forums.id = threads.forumid'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->Board->getId());
	$thread = $stm->getRow();
	$stm->close();
	}
catch (DBNoDataException $e)
	{
	$stm->close();
	$this->Output->setStatus(Output::NOT_FOUND);
	$this->showWarning($this->L10n->getText('Topic not found'));
	}

$this->ismod = $this->User->isGroup($thread['mods']) || $this->User->isMod();

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			posts
		WHERE
			threadid = ?
		');
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
					'.($this->ismod ? '' : 'posts.deleted = 0 AND').'
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
		$this->post = nat($this->posts-$this->Settings->getValue('max_posts'));
		}
	}

if ($thread['deleted'] == 1 && !$this->ismod)
	{
	$this->showWarning($this->L10n->getText('Topic not found'));
	}


$pages = $this->getPages();

if ($this->User->isOnline())
	{
	$this->Log->insert($thread['id'], $thread['lastdate']);
	}

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			posts.id,
			posts.userid,
			posts.username,
			posts.deleted,
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
			'.($this->ismod ? '' : 'AND posts.deleted = 0').'
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
$closed = (empty($thread['closed']) ? false : true);

foreach ($result as $data)
	{
	$postMenu = array();
	$modMenu = array();

	if ($this->User->isOnline() && $data['dat'] > $lastVisit)
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

	if ($data['deleted'] == 0 && !$closed && $thread['deleted'] == 0)
		{
		if ($this->User->isOnline())
			{
			if ($first && $this->post == 0)
				{
				if ($this->ismod or $this->User->isUser($data['userid']))
					{
					$modMenu[] = '<a href="'.$this->Output->createUrl('EditThread', array('thread' => $this->thread)).'">'.$this->L10n->getText('Edit topic').'</a>';
					}

				if ($this->ismod)
					{
					$modMenu[] = '<a href="'.$this->Output->createUrl('DelThread', array('thread' => $this->thread)).'">'.$this->L10n->getText('Delete topic').'</a>';
					}
				$first = false;
				}
			else
				{
				if ($this->ismod or $this->User->isUser($data['userid']))
					{
					$modMenu[] = '<a href="'.$this->Output->createUrl('EditPost', array('post' => $data['id'])).'">'.$this->L10n->getText('Edit post').'</a>';
					}

				if ($this->ismod)
					{
					$modMenu[] = '<a href="'.$this->Output->createUrl('DelPost', array('post' => $data['id'])).'">'.$this->L10n->getText('Delete post').'</a>';
					$modMenu[] = '<a href="'.$this->Output->createUrl('SplitThread', array('post' => $data['id'])).'">'.$this->L10n->getText('Split topic').'</a>';
					$modMenu[] = '<a href="'.$this->Output->createUrl('MovePosting', array('post' => $data['id'])).'">'.$this->L10n->getText('Move post').'</a>';
					}
				}
			}

		$postMenu[] = '<a href="'.$this->Output->createUrl('QuotePost', array('post' => $data['id'])).'">'.$this->L10n->getText('Quote post').'</a>';
		}
	elseif ($this->ismod && $data['deleted'] == 1 && !$closed)
		{
		$modMenu[] = '<a href="'.$this->Output->createUrl('DelPost', array('post' => $data['id'])).'">'.$this->L10n->getText('Recover post').'</a>';
		}

	if (count($modMenu) > 1)
		{
		$postMenu[] = $this->L10n->getText('Moderation').'<ul><li>'.implode('</li><li>', $modMenu).'</li></ul>';
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

if (!$closed && $thread['deleted'] == 0)
	{
	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('NewPost', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Post reply').'</a>');
	}
	
if ($this->ismod)
	{
	$this->addUserMenuEntry(''.$this->L10n->getText('Moderation').'
		<ul>
			<li><a href="'.$this->Output->createUrl('MoveThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Move topic').'</a></li>
			<li><a href="'.$this->Output->createUrl('DelThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Delete topic').'</a></li>
			<li><a href="'.$this->Output->createUrl('StickThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Stick topic').'</a></li>
			<li><a href="'.$this->Output->createUrl('CloseThread', array('thread' => $thread['id'])).'">'.($closed ? $this->L10n->getText('Open topic') : $this->L10n->getText('Close topic')).'</a></li>
		</ul>
		');
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
		</thead>
		<tfoot>
			<tr>
				<th colspan="2">
					'.$pages.'
				</th>
			</tr>
		</tfoot>
		'.$poll.'
		'.$postings.'
	</table>
	';
$this->setTitle($thread['name']);
$this->setBody($body);
}

protected function getPages()
	{
	$pages = '';

	if ($this->post > ($this->Settings->getValue('max_posts')))
		{
		$pages .= '<a href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread)).'">&laquo;</a>';
		}

	if ($this->post > 0)
		{
		$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread, 'post' => nat($this->post-$this->Settings->getValue('max_posts')))).'">&lsaquo;</a>';
		}

	for ($i = 0; $i < ($this->posts / $this->Settings->getValue('max_posts')) && ($this->posts / $this->Settings->getValue('max_posts')) > 1; $i++)
		{
		if ($this->post < $this->Settings->getValue('max_posts') * ($i-4))
			{
			$i = $this->Settings->getValue('max_posts') * ($i-4);
			continue;
			}
		elseif($this->post > $this->Settings->getValue('max_posts') * ($i+4))
			{
			continue;
			}

		if ($this->post == ($this->Settings->getValue('max_posts') * $i))
			{
			$pages .= ' <strong class="current-page">'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread, 'post' => ($this->Settings->getValue('max_posts') * $i))).'">'.($i+1).'</a>';
			}
		}

	if ($this->posts > $this->Settings->getValue('max_posts')+$this->post)
		{
		$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread, 'post' => ($this->Settings->getValue('max_posts')+$this->post))).'">&rsaquo;</a>';
		}

	$lastpage = $this->Settings->getValue('max_posts') *nat($this->posts / $this->Settings->getValue('max_posts'));

	if ($this->post < $lastpage-$this->Settings->getValue('max_posts'))
		{
		$pages .= ' <a href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread, 'post' => $lastpage)).'">&raquo;</a>';
		}

	return $pages;
	}

protected function getFiles($post)
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				attachments.id,
				attachments.name,
				attachments.type
			FROM
				attachments,
				post_attachments
			WHERE
				post_attachments.postid = ?
				AND post_attachments.attachment_id = attachments.id
			ORDER BY
				attachments.id DESC
			');
		$stm->bindInteger($post);
		$files = $stm->getRowSet();
		}
	catch(DBNoDataException $e)
		{
		$files = array();
		}


	$list = '';

	foreach ($files as $file)
		{
		if (strpos($file['type'], 'image/jpeg') === 0 ||
			strpos($file['type'], 'image/pjpeg') === 0 ||
			strpos($file['type'], 'image/png') === 0 ||
			strpos($file['type'], 'image/gif') === 0)
			{
			$list .= '<a href="'.$this->Output->createUrl('GetAttachment', array('file' => $file['id'])).'" rel="nofollow"><img src="'.$this->Output->createUrl('GetAttachmentThumb', array('file' => $file['id'])).'" alt="'.$file['name'].'" title="'.$file['name'].'" class="image" /></a> ';
			}
		else
			{
			$list .= '<a href="'.$this->Output->createUrl('GetAttachment', array('file' => $file['id'])).'">'.$file['name'].'</a> ';
			}
		}
	$stm->close();

	return $list;
	}

protected function getPoll()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				question
			FROM
				polls
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->thread);
		$question = $stm->getColumn();
		$stm->close();

		$stm = $this->DB->prepare
			('
			SELECT
				value,
				votes,
				id,
				(SELECT SUM(votes) FROM poll_values WHERE pollid = p.pollid) AS total
			FROM
				poll_values AS p
			WHERE
				pollid = ?
			ORDER BY
				votes DESC
			');
		$stm->bindInteger($this->thread);
		$options = $stm->getRowSet();

		$options = '';

		foreach ($stm->getRowSet() as $data)
			{
			if ($data['total'] == 0)
				{
				$percent = 0;
				}
			else
				{
				$percent = $data['votes'] / $data['total'] * 100;
				}

			$options .=
				'
				<tr>
					<th>'.$data['value'].'</th>
					<td>
						<div class="poll-bar" style="width:'.round($percent).'%">
							&nbsp;
						</div>
					</td>
					<td class="poll-percent">'.round($percent, 2).'&thinsp;%</td>
					<td class="poll-votes">'.$data['votes'].'</td>
				</tr>
				';
			}
		$stm->close();

		$body =
			'
			<tbody class="poll">
				<tr>
					<th rowspan="3">'.$this->L10n->getText('Poll').'</th>
					<td class="poll-question">'.$question.'</td>
				</tr>
				<tr>
					<td class="poll-options">
						<table style="width:100%;">
							'.$options.'
						</table>
					</td>
				</tr>
				<tr>
					<td class="poll-vote">
						<a href="'.$this->Output->createUrl('Poll', array('thread' => $this->thread, 'target' => $this->getName())).'">'.$this->L10n->getText('Vote').'</a>
					</td>
				</tr>
			</tbody>
			';

		return $body;
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return '';
		}
	}

}

?>
