<?php


class PrivatePostings extends Postings{


public function prepare(){

	if (!$this->User->isOnline())
		{
		$this->showWarning('Nur für Mitglieder');
		}

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
			threads.lastdate
		FROM
			threads,
			thread_user
		WHERE
			threads.id = '.$threadid.'
			AND threads.forumid = 0
			AND thread_user.threadid = threads.id
			AND thread_user.userid = '.$this->User->getId()
		);
	}
catch (SqlNoDataException $e)
		{
		$this->showWarning('Thema nicht gefunden.');
		}

$count = $this->Sql->numRows('posts WHERE posts.threadid = '.$threadid);

if ($post == -1)
	{
	if ($this->Log->isNew($threadid, $thread['lastdate']))
		{
		$post = $count - $this->Sql->numRows('posts WHERE posts.threadid = '.$threadid.' AND dat >= '.$this->Log->getTime($threadid));
		}
	else
		{
		$post = nat($count-Settings::MAX_POSTS);
		}
	}


$limit = $post.','.Settings::MAX_POSTS;

$pages = '';
	for ($i = 0; $i < ($count / Settings::MAX_POSTS) and ($count / Settings::MAX_POSTS) > 1; $i++)
		{
		if ($post == (Settings::MAX_POSTS * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$threadid.';post='.(Settings::MAX_POSTS * $i).'">'.($i+1).'</a>';
			}
		}

$next = ($count > Settings::MAX_POSTS+$post ? ' <a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$threadid.';post='.(Settings::MAX_POSTS+$post).'">&#187;</a>' : '');

$last = ($post > 0 ? '<a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$threadid.';post='.nat($post-Settings::MAX_POSTS).'">&#171;</a>' : '');

$this->Log->insert($thread['id'], $thread['lastdate']);


$recipients = $this->Sql->fetch
	('
	SELECT
		users.id,
		users.name
	FROM
		users,
		thread_user
	WHERE
		thread_user.threadid ='.$thread['id'].'
		AND thread_user.userid = users.id
	');

$users = array();
foreach ($recipients as $recipient)
	{
	$users[] = '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$recipient['id'].'">'.$recipient['name'].'</a>';
	}

try
	{
	$result = $this->Sql->fetch
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
			posts.threadid = '.$threadid.'
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


foreach ($result as $data)
	{
	$i = abs($i-1);
	$style = 'class="post'.$i.'"';

	$postid = $data['id'];

	$data['dat'] = formatDate($data['dat']);

	if ($data['editdate'] > 0)
		{
		$edited = '<div class="postedit">von <a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['editby'].'">'.$data['editorname'].'</a> am '.formatDate($data['editdate']).' geändert</div>';
		}
	else
		{
		$edited = '';
		}

	if ($first && $post == 0)
		{
		$edit_button = (($this->User->isUser($data['userid'])) ?
					' <a href="?page=EditPrivateThread;id='.$this->Board->getId().';thread='.$threadid.'"><span class="button">Thema ändern</span></a>' : '');
		$first = false;
		}
	else
		{
		$edit_button = (($this->User->isUser($data['userid'])) ?
					' <a href="?page=EditPrivatePost;id='.$this->Board->getId().';post='.$data['id'].'"><span class="button">ändern</span></a>' : '');
		}

	$quote_button = '<a href="?page=QuotePrivatePost;id='.$this->Board->getId().';post='.$postid.'"><span class="button">zitieren</span></a>';


	$poster = (!empty($data['userid']) ? '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['userid'].'">'.$data['username'].'</a>' : $data['username']);

	$avatar = (empty($data['avatar']) || !$this->User->isOnline() ? '' : '<img src="?page=GetFile;file='.$data['avatar'].'" class="avatar" />');

	if ($data['file'] == 1)
		{
		$files = $this->getFiles($data['id']);
		}
	else
		{
		$files = '';
		}

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
				'.$last.$pages.$next.'&nbsp;
			</td>
			<td class="pages">
			Schon dabei: '.implode(', ', $users).'
			</td>
			<td class="pages" style="text-align:right">
				'.$reply_button.'
			</td>
		</tr>
			'.$posts.'
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
				<a class="pathlink" href="?page=PrivateThreads;id='.$this->Board->getId().'">Private Themen</a>
			</td>
		</tr>
	</table>
	';
$this->setValue('title', $thread['name']);
$this->setValue('body', $body);
}

}
?>