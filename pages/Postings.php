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
	$this->showWarning($this->L10n->getText('Topic not found.'));
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
	$this->showWarning($this->L10n->getText('Topic not found.'));
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

$postings	= '';
$first 		= true;
$closed 	= (empty($thread['closed']) ? false : true);
$deleted 	= false;

$postcount	= 1;
$posttype 	= array('post');

foreach ($result as $data)
	{
	if($data['deleted'] == 1)
		{
		$posttype[] = 'deletedpost';
		$deleted = true;
		}
	else
		{
		$deleted = false;
		}

	$postid = $data['id'];

	if ($this->User->isOnline() && $data['dat'] > $lastVisit)
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

	if (!$deleted && !$closed && $thread['deleted'] == 0)
		{
		if ($this->User->isOnline())
			{
			if ($first && $this->post == 0)
				{
				$edit_button = (($this->ismod or $this->User->isUser($data['userid'])) ?
							' <a href="'.$this->Output->createUrl('EditThread', array('thread' => $this->thread)).'"><span>'.$this->L10n->getText('Edit topic').'</span></a>' : '');

				$del_button = ($this->ismod ?
							' <a href="'.$this->Output->createUrl('DelThread', array('thread' => $this->thread)).'"><span>'.$this->L10n->getText('Delete topic').'</span></a>' : '');

				$split_button = '';
				$move_button = '';

				$first = false;
				}
			else
				{
				$edit_button = (($this->ismod or $this->User->isUser($data['userid'])) ?
							' <a href="'.$this->Output->createUrl('EditPost', array('post' => $data['id'])).'"><span>'.$this->L10n->getText('Edit post').'</span></a>' : '');

				$del_button = ($this->ismod ?
							' <a href="'.$this->Output->createUrl('DelPost', array('post' => $data['id'])).'"><span>'.$this->L10n->getText('Delete post').'</span></a>' : '');

				$split_button = ($this->ismod ?
							' <a href="'.$this->Output->createUrl('SplitThread', array('post' => $data['id'])).'"><span>'.$this->L10n->getText('Split topic').'</span></a>' : '');

				$move_button = ($this->ismod ?
							' <a href="'.$this->Output->createUrl('MovePosting', array('post' => $data['id'])).'"><span>'.$this->L10n->getText('Move post').'</span></a>' : '');
				}
			}
		else
			{
			$edit_button = '';
			$del_button = '';
			$split_button = '';
			$move_button = '';
			}

		$quote_button = '<a href="'.$this->Output->createUrl('QuotePost', array('post' => $postid)).'"><span>'.$this->L10n->getText('Quote post').'</span></a>';
		}
	elseif($thread['deleted'] == 1)
		{
		$edit_button = '';
		$quote_button = '';
		$del_button = '';
		$split_button = '';
		$move_button = '';
		}
	else
		{
		$edit_button = '';
		$quote_button = '';
		$split_button = '';
		$move_button = '';

		if ($this->User->isOnline() && $deleted && !$closed)
			{
			$del_button = ($this->ismod ? ' <a href="'.$this->Output->createUrl('DelPost', array('post' => $data['id'])).'"><span>'.$this->L10n->getText('Recover post').'</span></a>' : '');
			}
		else
			{
			$del_button = '';
			}
		}

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
					<h3><a class="permalink" rel="bookmark" href="'.$this->Output->createUrl('Postings', array('thread' => $thread['id'], 'post' => ($this->post + $postcount - 1))).'"><strong>'.($this->post + $postcount).'</strong></a> <span>'.$this->L10n->getDateTime($data['dat']).'</span></h3>
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
						'.$del_button.$edit_button.$quote_button.$split_button.$move_button.'
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
	$poll = $this->getPoll();
	}
else
	{
	$poll = '';
	}

$thread_buttons = ($this->ismod ?
	'<a class="mod-option" href="'.$this->Output->createUrl('MoveThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Move topic').'</a>
	<a class="mod-option" href="'.$this->Output->createUrl('DelThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Delete topic').'</a>
	<a class="mod-option" href="'.$this->Output->createUrl('StickThread', array('thread' => $thread['id'])).'">'.$this->L10n->getText('Stick topic').'</a>
	<a class="mod-option" href="'.$this->Output->createUrl('CloseThread', array('thread' => $thread['id'])).'">'.($closed ? $this->L10n->getText('Open topic') : $this->L10n->getText('Close topic')).'</a>
	' : '');

$reply_button = (!$closed && $thread['deleted'] == 0 ? '<a class="newpost" href="'.$this->Output->createUrl('NewPost', array('thread' => $thread['id'])).'"><span>'.$this->L10n->getText('Post reply').'</span></a>' : '');


$body =
	'
	<div id="brd-main" class="main paged">

	
	'.$poll.'
	<div class="paged-head">
		<p class="paging"><span class="pages">'.$this->L10n->getText('Pages').':</span> '.$pages.'</p>
		<p class="posting">'.$reply_button.'</p>
	</div>

	<div class="main-head">
		<div class="thread-title">'.$thread['name'].'</div>
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

protected function getPages()
	{
	$pages = '';
	$firstitem = ' class="item1"';

	if ($this->post > ($this->Settings->getValue('max_posts')))
		{
		$pages .= '<a'.$firstitem.' href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread)).'">&laquo;</a>';
		$firstitem = '';
		}

	if ($this->post > 0)
		{
		$pages .= ' <a'.$firstitem.' href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread, 'post' => nat($this->post-$this->Settings->getValue('max_posts')))).'">&lsaquo;</a>';
		$firstitem = '';
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
			$pages .= ' <strong'.$firstitem.'>'.($i+1).'</strong>';
			$firstitem = '';
			}
		else
			{
			$pages .= ' <a'.$firstitem.' href="'.$this->Output->createUrl($this->getName(), array('thread' => $this->thread, 'post' => ($this->Settings->getValue('max_posts') * $i))).'">'.($i+1).'</a>';
			$firstitem = '';
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
				attachments.size,
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


	$list = '<p><table>';

	foreach ($files as $file)
		{
		if (strpos($file['type'], 'image/jpeg') === 0 ||
			strpos($file['type'], 'image/pjpeg') === 0 ||
			strpos($file['type'], 'image/png') === 0 ||
			strpos($file['type'], 'image/gif') === 0)
			{
			$list .= '<tr>
 			<td>
			<a href="'.$this->Output->createUrl('GetAttachment', array('file' => $file['id'])).'" rel="nofollow"><img src="'.$this->Output->createUrl('GetAttachmentThumb', array('file' => $file['id'])).'" alt="'.$file['name'].'" /></a>
 			</td>
			<td>'.round($file['size'] / 1024, 2).' KByte</td>
			</tr>';
			}
		else
			{
			$list .= '<tr>
 			<td><a href="'.$this->Output->createUrl('GetAttachment', array('file' => $file['id'])).'">'.$file['name'].'</a></td>
			<td>'.round($file['size'] / 1024, 2).' KByte</td>
			</tr>';
			}
		}
	$stm->close();

	return $list.'</table></p>';
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
					<th style="width:30%">
						'.$data['value'].'
					</th>
					<td>
						<div class="pollbar" style="width:'.round($percent).'%">
							&nbsp;
						</div>
					</td>
					<td style="width:30%">
						'.round($percent, 2).'% ('.$data['votes'].')
					</td>
				</tr>
				';
			}
		$stm->close();

		$body =
			'<div class="main-head">
				<h2><span>'.$question.'</span></h2>
			</div>
			<table>
				'.$options.'
			</table>
			<div class="main-foot">
				<p class="main-options">
				<a class="mod-option" href="'.$this->Output->createUrl('Poll', array('thread' => $this->thread, 'target' => $this->getName())).'">'.$this->L10n->getText('Abstimmen').'</a>
				</p>
			</div>
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
