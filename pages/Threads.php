<?php


class Threads extends Page{


protected $ismod 	= false;
protected $forum 	= 0;
protected $thread 	= 0;
protected $threads 	= 0;
protected $result 	= array();

public function prepare(){

try
	{
	$this->forum = $this->Io->getInt('forum');
	}
catch (IoRequestException $e)
	{
	$this->showWarning('Kein Forum angegeben.');
	}

try
	{
	$this->thread = $this->Io->getInt('thread');
	}
catch (IoRequestException $e)
	{
	$this->thread = 0;
	}


$limit = $this->thread.','.Settings::MAX_THREADS;

try
	{
	$forum = $this->Sql->fetchRow
		('
		SELECT
			forums.name,
			cats.name AS catname,
			cats.id AS catid,
			forums.mods
		FROM
			forums,
			forum_cat,
			cats
		WHERE
			forums.id = '.$this->forum.'
			AND forum_cat.forumid = forums.id
			AND forum_cat.catid = cats.id
			AND cats.boardid = '.$this->Board->getId()
		);
	}
catch (SqlNoDataException $e)
	{
	//$this->showWarning('Kein Forum gefunden.');

	// Falls das Forum nicht (mehr) im aktuellen Board ist versuchen wir es zu finden.
	// (und wir stellen sicher, daß wir nicht auf die gleiche Seite weiterleiten.)
	try
		{
		$id = $this->Sql->fetchValue
			('
			SELECT
				boardid
			FROM
				forums
			WHERE
				id = '.$this->forum.'
				AND boardid != '.$this->Board->getId()
			);

		$this->Io->redirect('Threads','forum='.$this->forum.';thread='.$this->thread, $id);
		}
	catch (SqlNoDataException $e)
		{
		$this->showWarning('Forum nicht gefunden.');
		}
	}

$this->ismod = $this->User->isGroup($forum['mods']) || $this->User->isMod();

$this->threads = $this->Sql->numRows('threads WHERE '.($this->ismod ? '' : 'threads.deleted = 0 AND').' (threads.forumid = '.$this->forum.' OR threads.movedfrom = '.$this->forum.')');

try
	{
	$this->result = $this->Sql->fetch
		('
		SELECT
			id,
			poll,
			name,
			lastdate,
			lastuserid,
			lastusername,
			firstdate,
			firstuserid,
			firstusername,
			closed,
			sticky,
			deleted,
			posts,
			forumid,
			movedfrom
		FROM
			threads
		WHERE
			(forumid = '.$this->forum.' OR movedfrom = '.$this->forum.')
			'.($this->ismod ? '' : 'AND deleted =  0').'
		ORDER BY
			sticky DESC,
			lastdate DESC
		LIMIT
			'.$limit
		);
	}
catch (SqlNoDataException $e)
	{
	$this->result = array();
	}


$pages = $this->getPages();

$next = ($this->threads > Settings::MAX_THREADS+$this->thread
	? ' <a href="?page=Threads;id='.$this->Board->getId().';thread='.(Settings::MAX_THREADS+$this->thread).';forum='.$this->forum.'">&#187;</a>'
	: '');

$last = ($this->thread > 0
	? '<a href="?page=Threads;id='.$this->Board->getId().';thread='.nat($this->thread-Settings::MAX_THREADS).';forum='.$this->forum.'">&#171;</a>'
	: '');

$threads = $this->listThreads();

$body =
	'
	<table class="frame" style="width:100%">
		<tr>
			<td class="title" colspan="2">Thema</td>
			<td class="title">Erster Beitrag</td>
			<td class="title">Beiträge</td>
			<td class="title">Letzter Beitrag</td>
		</tr>
		<tr>
			<td class="path" colspan="5">
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'">'.$this->Board->getName().'</a>
				&#187;
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'#cat'.$forum['catid'].'">'.$forum['catname'].'</a>
				&#187;
				<strong>'.$forum['name'].'</strong>
			</td>
		</tr>
		<tr>
			<td class="pages" colspan="4">'.$last.$pages.$next.'</td>
			<td class="pages">
			<a href="?page=NewThread;id='.$this->Board->getId().';forum='.$this->forum.'"><span class="button">Neues Thema</span></a>
			</td>
		</tr>
		'.$threads.'
		<tr>
			<td class="pages" colspan="4">'.$last.$pages.$next.'</td>
			<td class="pages">
			<a href="?page=NewThread;id='.$this->Board->getId().';forum='.$this->forum.'"><span class="button">Neues Thema</span></a>
			</td>
		</tr>
		<tr>
			<td class="path" colspan="5">
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'">'.$this->Board->getName().'</a>
				&#187;
				<a class="pathlink" href="?page=Forums;id='.$this->Board->getId().'#cat'.$forum['catid'].'">'.$forum['catname'].'</a>
				&#187;
				<strong>'.$forum['name'].'</strong>
			</td>
		</tr>
	</table>
	';

$this->setValue('title', $forum['name']);
$this->setValue('body', $body);
}

protected function listThreads()
	{
	$threads = '';

	foreach ($this->result as $data)
		{
		$thread_pages = '';
		for ($i = 0; $i < ($data['posts'] / Settings::MAX_POSTS) && ($data['posts'] / Settings::MAX_POSTS) > 1; $i++)
			{
			if ($i >= 6 && $i <= ($data['posts'] / Settings::MAX_POSTS) - 6)
				{
				$thread_pages .= ' ... ';
				$i = nat($data['posts'] / Settings::MAX_POSTS) - 6;
				continue;
				}

			$thread_pages .= ' <a href="?page=Postings;id='.$this->Board->getId().';thread='.$data['id'].';post='.(Settings::MAX_POSTS * $i).'">'.($i+1).'</a>';
			}

		$thread_pages = (!empty($thread_pages) ? '<span class="threadpages">&#171;'.$thread_pages.' &#187;</span>' : '');


		if ($this->User->isOnline() && $this->Log->isNew($data['id'], $data['lastdate']))
			{
			$data['name'] = '<span class="newthread">'.$data['name'].'</span>';
			}

		if($data['deleted'] == 1)
			{
			$data['name'] = '<span class="deletedthread">'.$data['name'].'</span>';
			}

		/** FIXME: Schlecht, wenn Thread in anderes Board verschoben wurde */
		if ($data['forumid'] != $this->forum)
			{
			$data['name'] = '<span class="movedthread">'.$data['name'].'</span>';
			}


		/** FIXME */
		$status = (!empty($data['poll'])    ? '<span class="poll"></span>' : '');
		$status .= (!empty($data['closed']) ? '<span class="closed"></span>' : '');
		$status .= (!empty($data['sticky']) ? '<span class="sticky"></span>' : '');


		$lastposter = (empty($data['lastuserid'])
			? $data['lastusername']
			: '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['lastuserid'].'">'.$data['lastusername'].'</a>');

		$firstposter = (empty($data['firstuserid'])
			? $data['firstusername']
			: '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$data['firstuserid'].'">'.$data['firstusername'].'</a>');

		$data['lastdate'] = formatDate($data['lastdate']);
		$data['firstdate'] = formatDate($data['firstdate']);

		$threads .=
			'
			<tr>
				<td class="threadiconcol">
					'.$status.'
				</td>
				<td class="forumcol">
					<div class="thread">
					<a href="?page=Postings;id='.$this->Board->getId().';thread='.$data['id'].'">'.$data['name'].'</a>
					</div>
					<div class="threadpages">
					'.$thread_pages.'
					</div>
				</td>
				<td class="lastpost">
					<div>von '.$firstposter.'</div>
					<div>'.$data['firstdate'].'</div>
				</td>
				<td class="countcol">
					'.$data['posts'].'
				</td>
				<td class="lastpost">
					<div>von '.$lastposter.'</div>
					<div><a href="?page=Postings;id='.$this->Board->getId().';thread='.$data['id'].';post=-1">'.$data['lastdate'].'</a></div>
				</td>
			</tr>
			';
			}

	return $threads;
	}

protected function getPages()
	{
	$pages = '';

	for ($i = 0; $i < ($this->threads / Settings::MAX_THREADS) && ($this->threads / Settings::MAX_THREADS) > 1; $i++)
		{
		if ($this->thread < Settings::MAX_THREADS * ($i-4))
			{
			$i = Settings::MAX_THREADS * ($i-4);
			continue;
			}
		elseif($this->thread > Settings::MAX_THREADS * ($i+4))
			{
			continue;
			}

		if ($this->thread == (Settings::MAX_POSTS * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page=Threads;id='.$this->Board->getId().';thread='.(Settings::MAX_THREADS * $i).';forum='.$this->forum.'">'.($i+1).'</a>';
			}
		}

	return $pages;
	}

}

?>