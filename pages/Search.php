<?php


class Search extends Form{

private $search 	= '';
protected $result 	= array();
private $thread 	= 0;


protected function setForm()
	{
	$this->setValue('title', 'Suche');

	$this->addSubmit('Finden');

	$this->addText('search', 'Suchbegriff', '', 50);
	$this->requires('search');
	$this->setLength('search', 3, 50);
	}

protected function checkForm()
	{
	$this->search = $this->Io->getString('search');

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
		$this->result = $this->Sql->fetch
		('
		(
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.posts,
				threads.lastuserid,
				threads.lastusername,
				threads.firstdate,
				threads.firstuserid,
				threads.firstusername,
				threads.closed,
				threads.sticky,
				threads.poll,
				threads.posts,
				MATCH (threads.name) AGAINST (\''.$this->Sql->escapeString($this->search).'\' IN BOOLEAN MODE) AS score,
				forums.id AS forumid,
				forums.name AS forumname
			FROM
				threads,
				forums
			WHERE MATCH
				(threads.name)
			AGAINST (\''.$this->Sql->escapeString($this->search).'\' IN BOOLEAN MODE)
			AND threads.forumid = forums.id
			AND threads.deleted = 0
			ORDER BY score DESC
		)
		UNION
		(
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.posts,
				threads.lastuserid,
				threads.lastusername,
				threads.firstdate,
				threads.firstuserid,
				threads.firstusername,
				threads.closed,
				threads.sticky,
				threads.poll,
				threads.posts,
				MATCH (posts.text) AGAINST (\''.$this->Sql->escapeString($this->search).'\' IN BOOLEAN MODE) as score,
				forums.id AS forumid,
				forums.name AS forumname
			FROM
				posts,
				threads,
				forums
			WHERE MATCH
				(posts.text)
			AGAINST (\''.$this->Sql->escapeString($this->search).'\' IN BOOLEAN MODE)
			AND posts.threadid = threads.id
			AND threads.forumid = forums.id
			AND threads.deleted = 0
			AND posts.deleted = 0
			GROUP BY threads.id
			ORDER BY score DESC
		)
		LIMIT '.$limit
		);
		}
	catch (SqlNoDataException $e)
		{
		$this->showWarning('Leider nichts gefunden');
		}
	}


protected function sendForm()
	{
	$this->setValue('title', 'Suche nach &quot;'.htmlspecialchars($this->search).'&quot;');

	$next = '&nbsp;<a href="?page=Search;id='.$this->Board->getId().';thread='.($this->Settings->getValue('max_threads')+$this->thread).';search='.urlencode($this->search).';submit=">&#187;</a>';

	$last = ($this->thread > 0 ? '<a href="?page=Search;id='.$this->Board->getId().';thread='.nat($this->thread-$this->Settings->getValue('max_threads')).';search='.urlencode($this->search).';submit=">&#171;</a>' : '');

	$threads = $this->listThreads();

	$body =
	'
	<table class="frame" style="width:100%">
		<tr>
			<td class="title" colspan="2">Thema</td>
			<td class="title">Erster Beitrag</td>
			<td class="title">Beiträge</td>
			<td class="title">Letzter Beitrag</td>
			<td class="title">Forum</td>
		</tr>
		<tr>
			<td class="pages" colspan="6">'.$last.$next.'&nbsp;</td>
		</tr>
		'.$threads.'
		<tr>
			<td class="pages" colspan="6">'.$last.$next.'&nbsp;</td>
		</tr>
	</table>
	';

	$this->setValue('body', $body);
	}

protected function listThreads()
	{
	$threads = '';

	foreach ($this->result as $data)
		{
		if($data['forumid'] == 0)
			{
			$target = 'PrivatePostings';
			$forum = '<a href="?page=PrivateThreads;id='.$this->Board->getId().'">Private Themen</a>';
			}
		else
			{
			$target = 'Postings';
			$forum = '<a href="?page=Threads;forum='.$data['forumid'].';id='.$this->Board->getId().'">'.$data['forumname'].'</a>';
			}


		$thread_pages = '';
		for ($i = 0; $i < ($data['posts'] / $this->Settings->getValue('max_posts')) && ($data['posts'] / $this->Settings->getValue('max_posts')) > 1; $i++)
			{
			if ($i >= 6 && $i <= ($data['posts'] / $this->Settings->getValue('max_posts')) - 6)
				{
				$thread_pages .= ' ... ';
				$i = nat($data['posts'] / $this->Settings->getValue('max_posts')) - 6;
				continue;
				}

			$thread_pages .= ' <a href="?page='.$target.';id='.$this->Board->getId().';thread='.$data['id'].';post='.($this->Settings->getValue('max_posts') * $i).'">'.($i+1).'</a>';
			}

		$thread_pages = (!empty($thread_pages) ? '<span class="threadpages">&#171;'.$thread_pages.' &#187;</span>' : '');


		if ($this->User->isOnline() && $this->Log->isNew($data['id'], $data['lastdate']))
			{
			$data['name'] = '<span class="newthread">'.$data['name'].'</span>';
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
					<a href="?page='.$target.';id='.$this->Board->getId().';thread='.$data['id'].'">'.$data['name'].'</a>
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
					<div><a href="?page='.$target.';id='.$this->Board->getId().';thread='.$data['id'].';post=-1">'.$data['lastdate'].'</a></div>
				</td>
				<td class="forums">
					'.$forum.'
				</td>
			</tr>
			';
		}

	return $threads;
	}

}

?>