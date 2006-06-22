<?php


class Postings extends Page{

protected $ismod 		= false;
protected $thread		= 0;
protected $post 		= 0;
protected $posts 		= 0;

public function prepare(){

try
	{
	$this->thread = $this->Io->getInt('thread');
	}
catch (IoRequestException $e)
	{
	$this->showWarning('Kein Thema angegeben');
	}

try
	{
	$this->post = $this->Io->getInt('post');
	}
catch (IoRequestException $e)
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
	// Falls das Thema nicht (mehr) im aktuellen Board ist versuchen wir es zu finden.
	// (und wir stellen sicher, daß wir nicht auf die gleiche Seite weiterleiten.)
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.boardid
			FROM
				threads,
				forums
			WHERE
				threads.id = ?
				AND forums.id = threads.forumid
				AND forums.boardid != ?'
			);
		$stm->bindInteger($this->thread);
		$stm->bindInteger($this->Board->getId());
		$boardid = $stm->getColumn();
		$stm->close();

		$this->Io->redirect('Postings','thread='.$this->thread.';post='.$this->post, $boardid);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Thema nicht gefunden.');
		}
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
			'.($this->ismod ? '' : 'posts.deleted = 0 AND').
			' posts.threadid = ?');
	$stm->bindInteger($this->thread);
	$this->posts = $stm->getColumn();
	$stm->close();
	}
catch (DBNoDataException $e)
	{
	$stm->close();
	$this->posts = 0;
	}


if ($this->post == -1)
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
			$stm->bindInteger($this->Log->getTime($this->thread));
			$this->post = $this->posts - $stm->getColumn();
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			$this->post = $this->posts;
			}
		}
	else
		{
		$this->post = nat($this->posts-$this->Settings->getValue('max_posts'));
		}
	}


$limit = $this->post.','.$this->Settings->getValue('max_posts');

if ($thread['deleted'] == 1 && !$this->ismod)
	{
	$this->showWarning('Thema nicht gefunden.');
	}


$pages = $this->getPages();

$next = ($this->posts > $this->Settings->getValue('max_posts')+$this->post ? ' <a href="?page=Postings;id='.$this->Board->getId().';thread='.$this->thread.';post='.($this->Settings->getValue('max_posts')+$this->post).'">&#187;</a>' : '');

$last = ($this->post > 0 ? '<a href="?page=Postings;id='.$this->Board->getId().';thread='.$this->thread.';post='.nat($this->post-$this->Settings->getValue('max_posts')).'">&#171;</a>' : '');


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
		ORDER BY
			posts.dat ASC
		LIMIT
			'.$limit
		);
	$stm->bindInteger($this->thread);
	$result = $stm->getRowSet();
	}
catch (DBNoDataException $e)
	{
	$result = array();
	}

$postings	= '';
$i 		= 2;
$first 	= true;
$closed 	= (empty($thread['closed']) ? false : true);
$deleted 	= false;

foreach ($result as $data)
	{
	$i = abs($i-1);

	if($data['deleted'] == 1)
		{
		$style = 'class="deletedpost"';
		$i = abs($i-1);
		$deleted = true;
		}
	else
		{
		$style = 'class="post'.$i.'"';
		$deleted = false;
		}

	$postid = $data['id'];

	$data['dat'] = formatDate($data['dat']);

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

	if (!$deleted && !$closed && $thread['deleted'] == 0)
		{
		if ($this->User->isOnline())
			{
			if ($first && $this->post == 0)
				{
				$edit_button = (($this->ismod or $this->User->isUser($data['userid'])) ?
							' <a href="?page=EditThread;id='.$this->Board->getId().';thread='.$this->thread.'"><span class="button">Thema ändern</span></a>' : '');

				$del_button = ($this->ismod ?
							' <a href="?page=DelThread;id='.$this->Board->getId().';thread='.$this->thread.'"><span class="button">Thema löschen</span></a>' : '');

				$split_button = '';
				$move_button = '';

				$first = false;
				}
			else
				{
				$edit_button = (($this->ismod or $this->User->isUser($data['userid'])) ?
							' <a href="?page=EditPost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">ändern</span></a>' : '');

				$del_button = ($this->ismod ?
							' <a href="?page=DelPost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">löschen</span></a>' : '');

				$split_button = ($this->ismod ?
							' <a href="?page=SplitThread;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">abzweigen</span></a>' : '');

				$move_button = ($this->ismod ?
							' <a href="?page=MovePosting;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">verschieben</span></a>' : '');
				}
			}
		else
			{
			$edit_button = '';
			$del_button = '';
			$split_button = '';
			$move_button = '';
			}

		$quote_button = '<a href="?page=QuotePost;id='.$this->Board->getId().';post='.$postid.'"><span class="button">zitieren</span></a>';
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
			$del_button = ($this->ismod ? ' <a href="?page=DelPost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">wiederherstellen</span></a>' : '');
			}
		else
			{
			$del_button = '';
			}
		}

	$poster = (!empty($data['userid']) ? '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['userid'].'">'.$data['name'].'</a>' : $data['username']);

	$avatar = (empty($data['avatar']) || !$this->User->isOnline() ? '' : '<img src="?page=GetAvatar;user='.$data['userid'].'" class="avatar" alt="" />');

	if ($data['file'] == 1 && $this->User->isOnline())
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
			<td '.$style.' style="vertical-align:top;width:150px;">
				<div class="postdate">'.$data['dat'].'</div>
			</td>
			<td '.$style.'>
				<div class="postbuttons">'.$quote_button.$edit_button.$del_button.$split_button.$move_button.'</div>
			</td>
		</tr>
		<tr>
			<td '.$style.' rowspan="2" colspan="2">
				'.$data['text'].$files.'
			</td>
		</tr>
		<tr>
			<td  '.$style.' rowspan="2" style="vertical-align:top;text-align:center;width:150px;">
				<div style="height:100px;width:150px;overflow:hidden;">'.$avatar.'</div>
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
	$thisPoll = new Poll($thread['id']);
	$poll = $thisPoll->showPoll();
	}
else
	{
	$poll = '';
	}

$thread_buttons = ($this->ismod ?
	'<tr><td class="pages" colspan="3"><a href="?page=DelThread;id='.$this->Board->getId().';thread='.$thread['id'].'"><span class="button">Thema löschen</span></a>
	<a href="?page=MoveThread;id='.$this->Board->getId().';thread='.$thread['id'].'"><span class="button">Thema verschieben</span></a>
	<a href="?page=CloseThread;id='.$this->Board->getId().';thread='.$thread['id'].'"><span class="button">Thema schließen</span></a>
	<a href="?page=StickThread;id='.$this->Board->getId().';thread='.$thread['id'].';stick=1"><span class="button">Thema festsetzen</span></a></td></tr>' : '');

$reply_button = (!$closed && $thread['deleted'] == 0 ? '<a href="?page=NewPost;id='.$this->Board->getId().';thread='.$thread['id'].'"><span class="button">antworten</span></a>' : '');

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
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'#cat'.$thread['catid'].'">'.$thread['catname'].'</a>
				&#187;
				<a class="pathlink" href="?page=Threads;id='.$this->Board->getId().';forum='.$thread['forumid'].'">'.$thread['forumname'].'</a>
			</td>
		</tr>
		<tr>
			<td class="pages">
				'.$last.$pages.$next.'&nbsp;
			</td>
			<td class="pages" colspan="2" style="text-align:right">
				'.$reply_button.'
			</td>
		</tr>
			'.$postings.'
		<tr>
			<td class="pages">
				'.$last.$pages.$next.'&nbsp;
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
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'#cat'.$thread['catid'].'">'.$thread['catname'].'</a>
				&#187;
				<a class="pathlink" href="?page=Threads;id='.$this->Board->getId().';forum='.$thread['forumid'].'">'.$thread['forumname'].'</a>
			</td>
		</tr>
		'.$thread_buttons.'
	</table>
	';
$this->setValue('title', $thread['name']);
$this->setValue('body', $body);
}

protected function getPages()
	{
	$pages = '';

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
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page=Postings;id='.$this->Board->getId().';thread='.$this->thread.';post='.($this->Settings->getValue('max_posts') * $i).'">'.($i+1).'</a>';
			}
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


	$list = '<table class="frame" style="margin:10px;font-size:9px;">';

	foreach ($files as $file)
		{
		if (strpos($file['type'], 'image/jpeg') === 0 ||
			strpos($file['type'], 'image/pjpeg') === 0 ||
			strpos($file['type'], 'image/png') === 0 ||
			strpos($file['type'], 'image/gif') === 0)
			{
			$list .= '<tr>
 			<td style="padding:5px;" colspan="2">
			<a href="?page=GetAttachment;file='.$file['id'].'" onclick="return !window.open(this.href);" rel="nofollow"><img src="?page=GetAttachmentThumb;file='.$file['id'].'" alt="'.$file['name'].'" class="image" /></a>
 			</td>
			</tr>';
			}
		else
			{
			$list .= '<tr>
 			<td style="padding:5px;"><a class="link" onclick="return !window.open(this.href);" href="?page=GetAttachment;file='.$file['id'].'">'.$file['name'].'</a></td>
			<td style="text-align:right;padding:5px;">'.round($file['size'] / 1024, 2).' KByte</td>
			</tr>';
			}
		}
	$stm->close();

	return $list.'</table>';
	}

}
?>