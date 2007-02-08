<?php


class Search extends Form{

private $search 	= '';
private $thread 	= 0;

private $globalSearch 	= '';


protected function setForm()
	{
	$this->setValue('title', 'Suche');

	$this->addSubmit('Finden');

	$this->addText('search', 'Suchbegriff', '', 50);
	$this->requires('search');
	$this->setLength('search', 3, 50);

	$this->addCheckBox('globalSearch', 'in allen Boards suchen', false);

	$this->isCheckSecurityToken(false);
	$this->isCheckAntiSpamHash(false);
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

	if (!$this->Io->isRequest('globalSearch'))
		{
		$this->globalSearch = 'AND forums.boardid = '.$this->Board->getId();
		}
	}

private function getResult()
	{
	$result = array();

	$limit = $this->thread.','.$this->Settings->getValue('max_threads');

	try
		{
		$stm = $this->DB->prepare
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
				MATCH (threads.name) AGAINST (? IN BOOLEAN MODE) AS score,
				forums.id AS forumid,
				forums.name AS forumname,
				summary
			FROM
				threads,
				forums
			WHERE MATCH
				(threads.name)
			AGAINST (? IN BOOLEAN MODE)
			AND threads.forumid = forums.id
			AND threads.deleted = 0
			'.$this->globalSearch.'
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
				MATCH (posts.text) AGAINST (? IN BOOLEAN MODE) as score,
				forums.id AS forumid,
				forums.name AS forumname,
				summary
			FROM
				posts,
				threads,
				forums
			WHERE MATCH
				(posts.text)
			AGAINST (? IN BOOLEAN MODE)
			AND posts.threadid = threads.id
			AND threads.forumid = forums.id
			AND threads.deleted = 0
			AND posts.deleted = 0
			'.$this->globalSearch.'
			GROUP BY threads.id
			ORDER BY score DESC
		)
		LIMIT '.$limit
		);
		$stm->bindString($this->search);
		$stm->bindString($this->search);
		$stm->bindString($this->search);
		$stm->bindString($this->search);
		$result = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$this->showWarning('Leider nichts gefunden');
		}

	return $result;
	}

protected function sendForm()
	{
	$this->setValue('title', 'Suche nach &quot;'.htmlspecialchars($this->search).'&quot;');

	$params = ';search='.urlencode($this->search).';submit='.($this->Io->isRequest('globalSearch') ? ';globalSearch=' : '');

	$next = '&nbsp;<a href="?page=Search;id='.$this->Board->getId().';thread='.($this->Settings->getValue('max_threads')+$this->thread).$params.'">&#187;</a>';

	$last = ($this->thread > 0 ? '<a href="?page=Search;id='.$this->Board->getId().';thread='.nat($this->thread-$this->Settings->getValue('max_threads')).$params.'">&#171;</a>' : '');

	$threads = $this->ThreadList->getList($this->getResult());

	if (count($this->warning) == 0)
		{
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
	
		$this->appendOutput($body);
		}

	$this->showForm();
	}


}

?>