<?php


class Threads extends Page{


protected $ismod 		= false;
protected $forum 		= 0;
protected $thread 		= 0;
protected $threads 		= 0;
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
	$this->thread = nat($this->Io->getInt('thread'));
	}
catch (IoRequestException $e)
	{
	$this->thread = 0;
	}


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
				forums.boardid,
				boards.host
			FROM
				forums,
				boards
			WHERE
				forums.id = ?
				AND forums.boardid != ?
				AND forums.boardid = boards.id'
			);
		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->Board->getId());
		$board = $stm->getRow();
		$stm->close();

		$this->Io->redirectToUrl('http'.(!getenv('HTTPS') ? '' : 's').'://'.$board['host'].'/?page=Threads;id='.$board['boardid'].';forum='.$this->forum.';thread='.$this->thread);
		}
	catch (DBException $e)
		{
		$stm->close();
		$this->Io->setStatus(Io::NOT_FOUND);
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
			forumid = ?
		');
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
		(
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
				forumid = ?
				'.($this->ismod ? '' : 'AND deleted =  0').'
				AND sticky = 1
		)
		UNION
		(
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
				forumid = ?
				'.($this->ismod ? '' : 'AND deleted =  0').'
				AND counter BETWEEN ? AND ?
				AND sticky = 0
		)
		ORDER BY
			sticky DESC,
			lastdate DESC
		');
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->forum);

	$stm->bindInteger($this->threads-$this->Settings->getValue('max_threads')-$this->thread);
	$stm->bindInteger($this->threads-$this->thread-1);

	$this->result = $stm->getRowSet();
	}
catch (DBNoDataException $e)
	{
	$this->result = array();
	}


$pages = $this->getPages();

$threads = $this->listThreads();
$stm->close();

$body =
	'<script type="text/javascript">
		/* <![CDATA[ */
		function writeText(text)
			{
			var pos;
			pos = document;
			while ( pos.lastChild && pos.lastChild.nodeType == 1 )
				pos = pos.lastChild;
			pos.parentNode.appendChild( document.createTextNode(text));
			}
		/* ]]> */
	</script>
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
			<td class="pages" colspan="4">'.$pages.'</td>
			<td class="pages">
			<a href="?page=NewThread;id='.$this->Board->getId().';forum='.$this->forum.'"><span class="button">Neues Thema</span></a>
			</td>
		</tr>
		'.$threads.'
		<tr>
			<td class="pages" colspan="4">'.$pages.'</td>
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
			$data['name'] = '<span class="newthread">neu</span>'.$data['name'];
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
							/* <![CDATA[ */
							writeText("'.$data['summary'].'");
							/* ]]> */
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

	if ($this->thread > ($this->Settings->getValue('max_threads')))
		{
		$pages .= '<a href="?page='.$this->getName().';id='.$this->Board->getId().';forum='.$this->forum.'">&laquo;</a>';
		}

	if ($this->thread > 0)
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';forum='.$this->forum.';thread='.nat($this->thread-$this->Settings->getValue('max_threads')).'">&lsaquo;</a>';
		}

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
			$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';forum='.$this->forum.';thread='.($this->Settings->getValue('max_threads') * $i).'">'.($i+1).'</a>';
			}
		}

	if ($this->threads > $this->Settings->getValue('max_threads')+$this->thread)
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';forum='.$this->forum.';thread='.($this->Settings->getValue('max_posts')+$this->thread).'">&rsaquo;</a>';
		}

	$lastpage = $this->Settings->getValue('max_threads') *nat($this->threads / $this->Settings->getValue('max_threads'));

	if ($this->thread < $lastpage-$this->Settings->getValue('max_threads'))
		{
		$pages .= ' <a href="?page='.$this->getName().';id='.$this->Board->getId().';forum='.$this->forum.';thread='.$lastpage.'">&raquo;</a>';
		}

	return $pages;
	}

}

?>