<?php


class Threads extends Page{


protected $ismod 		= false;
protected $forum 		= 0;
protected $thread 	= 0;
protected $threads 	= 0;
protected $result 		= array();

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


$limit = $this->thread.','.$this->Settings->getValue('max_threads');

try
	{
	$stm = $this->DB->prepare
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
			forums.id = ?
			AND forum_cat.forumid = forums.id
			AND forum_cat.catid = cats.id
			AND cats.boardid = ?'
		);
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->Board->getId());
	$forum = $stm->getRow();
	$stm->close();
	}
catch (DBException $e)
	{
	$stm->close();
	// Falls das Forum nicht (mehr) im aktuellen Board ist versuchen wir es zu finden.
	// (und wir stellen sicher, daß wir nicht auf die gleiche Seite weiterleiten.)
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				boardid
			FROM
				forums
			WHERE
				id = ?
				AND boardid != ?'
			);
		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->Board->getId());
		$boardid = $stm->getColumn();
		$stm->close();

		$this->Io->redirect('Threads','forum='.$this->forum.';thread='.$this->thread, $boardid);
		}
	catch (DBException $e)
		{
		$stm->close();
		$this->showWarning('Forum nicht gefunden.');
		}
	}

$this->ismod = $this->User->isGroup($forum['mods']) || $this->User->isMod();

try
	{
	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			threads
		WHERE
			'.($this->ismod ? '' : 'threads.deleted = 0 AND').
			' (threads.forumid = ? OR threads.movedfrom = ?)');
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->forum);
	$this->threads = $stm->getColumn();
	$stm->close();
	}
catch (DBException $e)
	{
	$stm->close();
	$this->threads = 0;
	}

try
	{
	$stm = $this->DB->prepare
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
			movedfrom,
			summary
		FROM
			threads
		WHERE
			(forumid = ? OR movedfrom = ?)
			'.($this->ismod ? '' : 'AND deleted =  0').'
		ORDER BY
			sticky DESC,
			lastdate DESC
		LIMIT
			'.$limit
		);
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->forum);
	$this->result = $stm->getRowSet();
	}
catch (DBNoDataException $e)
	{
	$this->result = array();
	}


$pages = $this->getPages();

$next = ($this->threads > $this->Settings->getValue('max_threads')+$this->thread
	? ' <a href="?page=Threads;id='.$this->Board->getId().';thread='.($this->Settings->getValue('max_threads')+$this->thread).';forum='.$this->forum.'">&#187;</a>'
	: '');

$last = ($this->thread > 0
	? '<a href="?page=Threads;id='.$this->Board->getId().';thread='.nat($this->thread-$this->Settings->getValue('max_threads')).';forum='.$this->forum.'">&#171;</a>'
	: '');

$threads = $this->listThreads();
$stm->close();

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
		for ($i = 0; $i < ($data['posts'] / $this->Settings->getValue('max_posts')) && ($data['posts'] / $this->Settings->getValue('max_posts')) > 1; $i++)
			{
			if ($i >= 6 && $i <= ($data['posts'] / $this->Settings->getValue('max_posts')) - 6)
				{
				$thread_pages .= ' ... ';
				$i = nat($data['posts'] / $this->Settings->getValue('max_posts')) - 6;
				continue;
				}

			$thread_pages .= ' <a href="?page=Postings;id='.$this->Board->getId().';thread='.$data['id'].';post='.($this->Settings->getValue('max_posts') * $i).'">'.($i+1).'</a>';
			}

		$thread_pages = (!empty($thread_pages) ? '<span class="threadpages">&#171;'.$thread_pages.' &#187;</span>' : '');


		$data['name'] = cutString($data['name'], 80);

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
				<td class="forumcol"
					 onmouseover="javascript:document.getElementById(\'summary'.$data['id'].'\').style.visibility=\'visible\'"
					 onmouseout="javascript:document.getElementById(\'summary'.$data['id'].'\').style.visibility=\'hidden\'">
					<div class="thread">
					<a href="?page=Postings;id='.$this->Board->getId().';thread='.$data['id'].'">'.$data['name'].'</a>
					</div>
					<div class="threadpages">
					'.$thread_pages.'
					</div>
				</td>
				<td class="lastpost">
					<div class="summary" style="visibility:hidden;" id="summary'.$data['id'].'">
					<script type="text/javascript">
						var summary = document.createTextNode(\''.$data['summary'].'\');
						var view = document.getElementById(\'summary'.$data['id'].'\');
						view.appendChild(summary);
					</script>
					</div>
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

	for ($i = 0; $i < ($this->threads / $this->Settings->getValue('max_threads')) && ($this->threads / $this->Settings->getValue('max_threads')) > 1; $i++)
		{
		if ($this->thread < $this->Settings->getValue('max_threads') * ($i-4))
			{
			$i = $this->Settings->getValue('max_threads') * ($i-4);
			continue;
			}
		elseif($this->thread > $this->Settings->getValue('max_threads') * ($i+4))
			{
			continue;
			}

		if ($this->thread == ($this->Settings->getValue('max_threads') * $i))
			{
			$pages .= ' <strong>'.($i+1).'</strong>';
			}
		else
			{
			$pages .= ' <a href="?page=Threads;id='.$this->Board->getId().';thread='.($this->Settings->getValue('max_threads') * $i).';forum='.$this->forum.'">'.($i+1).'</a>';
			}
		}

	return $pages;
	}

}

?>