<?php

class SiteMap extends Page{

private $links = array();

public function prepare()
	{
	if (!$this->User->isLevel(3))
		{
		die('kein Zugriff!');
		}

	$boards = $this->DB->getColumnSet
		('
		SELECT
			id
		FROM
			boards
		');

	foreach($boards as $board)
		{
		$this->links[] = 'http://www.laber-land.de/?page=Forums;id='.$board;
		$this->links[] = 'http://www.laber-land.de/?page=Recent;id='.$board;
		}

	$threads = $this->DB->getRowSet
		('
		SELECT
			threads.id,
			forums.boardid
		FROM
			threads,
			forums
		WHERE
			threads.forumid = forums.id
		ORDER BY
			threads.lastdate DESC
		');

	foreach($threads as $thread)
		{
		$this->links[] = 'http://www.laber-land.de/?page=Postings;id='.$thread['boardid'].';thread='.$thread['id'];
		}

	$users = $this->DB->getColumnSet
		('
		SELECT
			id
		FROM
			users
		');

	foreach($users as $user)
		{
		$this->links[] = 'http://www.laber-land.de/?page=ShowUser;id=1;user='.$user;
		}

	$forums = $this->DB->getRowSet
		('
		SELECT
			id,
			boardid
		FROM
			forums
		ORDER BY
			lastdate DESC
		');

	foreach($forums as $forum)
		{
		$this->links[] = 'http://www.laber-land.de/?page=Threads;id='.$forum['boardid'].';forum='.$forum['id'];
		}
	}


public function show()
	{
	echo count($this->links);
	file_put_contents('/sitemap.txt', implode("\n", $this->links));
	}


}


?>