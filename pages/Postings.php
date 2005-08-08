<?php


class Postings extends Page{

private $ismod = false;

public function prepare(){

try
	{
	$threadid = $this->Io->getInt('thread');
	}
catch (IoRequestException $e)
	{
	$this->showWarning('Kein Thema angegeben');
	}

try
	{
	$post = $this->Io->getInt('post');
	}
catch (IoRequestException $e)
	{
	$post = 0;
	}

try
	{
	$thread = $this->Sql->fetchRow
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
			threads.id = '.$threadid.'
			AND threads.forumid = forum_cat.forumid
			AND forum_cat.catid = cats.id
			AND cats.boardid = '.$this->Board->getId().'
			AND forums.id = threads.forumid'
		);
	}
catch (SqlNoDataException $e)
	{
	// Falls das Thema nicht (mehr) im aktuellen Board ist versuchen wir es zu finden.
	// (und wir stellen sicher, daß wir nicht auf die gleiche Seite weiterleiten.)
	try
		{
		$id = $this->Sql->fetchValue
			('
			SELECT
				forums.boardid
			FROM
				threads,
				forums
			WHERE
				threads.id = '.$threadid.'
				AND forums.id = threads.forumid
				AND forums.boardid != '.$this->Board->getId()
			);

		$this->Io->redirect('Postings','thread='.$threadid.';post='.$post, $id);
		}
	catch (SqlNoDataException $e)
		{
		$this->showWarning('Thema nicht gefunden.');
		}
	}

$this->ismod = $this->User->isGroup($thread['mods']) || $this->User->isMod();

$count = $this->Sql->numRows('posts WHERE '.($this->ismod ? '' : 'posts.deleted = 0 AND').' posts.threadid = '.$threadid);


if ($post == -1)
	{
	if ($this->Log->isNew($threadid, $thread['lastdate']))
		{
		$post = $count - $this->Sql->numRows('posts WHERE '.($this->ismod ? '' : 'posts.deleted = 0 AND').' posts.threadid = '.$threadid.' AND dat >= '.$this->Log->getTime($threadid));
		}
	else
		{
		$post = nat($count-Settings::MAX_POSTS);
		}
	}


$limit = $post.','.Settings::MAX_POSTS;

if ($thread['deleted'] == 1 && !$this->ismod)
	{
	$this->showWarning('Thema nicht gefunden.');
	}


$pages = '';
	for ($i = 0; $i < ($count / Settings::MAX_POSTS) and ($count / Settings::MAX_POSTS) > 1; $i++)
		{
		if ($post == (Settings::MAX_POSTS * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page=Postings;id='.$this->Board->getId().';thread='.$threadid.';post='.(Settings::MAX_POSTS * $i).'">'.($i+1).'</a>';
			}
		}

$next = ($count > Settings::MAX_POSTS+$post ? ' <a href="?page=Postings;id='.$this->Board->getId().';thread='.$threadid.';post='.(Settings::MAX_POSTS+$post).'">&#187;</a>' : '');

$last = ($post > 0 ? '<a href="?page=Postings;id='.$this->Board->getId().';thread='.$threadid.';post='.nat($post-Settings::MAX_POSTS).'">&#171;</a>' : '');

if ($this->User->isOnline())
	{
	$this->Log->insert($thread['id'], $thread['lastdate']);
	}

try
	{
	$result = $this->Sql->fetch
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
			editors.name AS editorname,
			posts.text
		FROM
			posts
				LEFT JOIN users
					ON posts.userid = users.id
				LEFT JOIN users AS editors
					ON posts.editby = editors.id
		WHERE
			posts.threadid = '.$threadid.'
			'.($this->ismod ? '' : 'AND posts.deleted = 0').'
		ORDER BY
			posts.dat ASC
		LIMIT
			'.$limit
		);
	}
catch (SqlNoDataException $e)
	{
	$result = array();
	}


$posts 		= '';
$i 		= 2;
$first 		= true;
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
	$null = '';

	$data['dat'] = formatDate($data['dat']);

	if ($data['editdate'] > 0)
		{
		$edited = '<div class="postedit">von <a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['editby'].'">'.$data['editorname'].'</a> am '.formatDate($data['editdate']).' geändert</div>';
		}
	else
		{
		$edited = '';
		}

	if (!$deleted && !$closed && $thread['deleted'] == 0)
		{
		if ($this->User->isOnline())
			{
			if ($first && $post == 0)
				{
				$edit_button = (($this->ismod or $this->User->isUser($data['userid'])) ?
							' <a href="?page=EditThread;id='.$this->Board->getId().';thread='.$threadid.'"><span class="button">Thema ändern</span></a>' : '');

				$del_button = ($this->ismod ?
							' <a href="?page=DelThread;id='.$this->Board->getId().';thread='.$threadid.'"><span class="button">Thema löschen</span></a>' : '');
				$first = false;
				}
			else
				{
				$edit_button = (($this->ismod or $this->User->isUser($data['userid'])) ?
							' <a href="?page=EditPost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">ändern</span></a>' : '');

				$del_button = ($this->ismod ?
							' <a href="?page=DelPost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">löschen</span></a>' : '');
				}
			}
		else
			{
			$edit_button = '';
			$del_button = '';
			}

		$quote_button = '<a href="?page=QuotePost;id='.$this->Board->getId().';post='.$postid.'"><span class="button">zitieren</span></a>';
		}
	elseif($thread['deleted'] == 1)
		{
		$edit_button = '';
		$quote_button = '';
		$del_button = '';
		}
	else
		{
		$edit_button = '';
		$quote_button = '';

		if ($this->User->isOnline() && $deleted && !$closed)
			{
			$del_button = ($this->ismod ?
				' <a href="?page=DelPost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">wiederherstellen</span></a>' : '');
			}
		else
			{
			$del_button = '';
			}
		}

	$poster = (!empty($data['userid']) ? '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['userid'].'">'.$data['username'].'</a>' : $data['username']);

	$avatar = (empty($data['avatar']) ? '' : '<img src="'.$data['avatar'].'" class="avatar" alt="" />');

	$posts .=
		'
		<tr>
			<td '.$style.' rowspan="2" style="vertical-align:top;width:150px;">
				<div class="postname">'.$poster.'</div>
			</td>
			<td '.$style.' style="vertical-align:top;">
				<div class="postdate">'.$data['dat'].'</div>
			</td>
			<td '.$style.'>
				<div class="postbuttons">'.$quote_button.$edit_button.$del_button.'</div>
			</td>
		</tr>
		<tr>
			<td '.$style.' rowspan="2" colspan="2">
				'.$data['text'].'
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

if ($thread['poll'] == 1)
	{
	/** Poll sollte extra schließbar sein */
	$this->Poll = new Poll($thread['id']);
	$poll = $this->Poll->showPoll();
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
				<!-- &#187;
				<strong>'.$thread['name'].'</strong> -->
			</td>
		</tr>
		<tr>
			<td class="pages">
				'.$last.$pages.$next.'&nbsp;
			</td>
			<td class="pages" colspan="2" style="text-align:right">
				<!-- <a href="?page=NewThread;id='.$this->Board->getId().';forum='.$thread['forumid'].'"><span class="button">Neues Thema</span></a> -->'.$reply_button.'
			</td>
		</tr>
			'.$posts.'
		<tr>
			<td class="pages">
				'.$last.$pages.$next.'&nbsp;
			</td>
			<td class="pages" colspan="2" style="text-align:right">
				<!-- <a href="?page=NewThread;id='.$this->Board->getId().';forum='.$thread['forumid'].'"><span class="button">Neues Thema</span></a> -->'.$reply_button.'
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
				<!-- &#187;
				<strong>'.$thread['name'].'</strong> -->
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