<?php

class UpdateCounter extends Page{


public function prepare()
	{
	if (!$this->User->isAdmin())
		{
		$this->showWarning('Zutritt verboten!');
		}

	$this->updatePostCounter();
	$this->updateThreadCounter();

	$this->setValue('body', 'fertig');
	}

private function updatePostCounter()
	{
	$this->DB->execute('LOCK TABLES posts WRITE');

	$posts = $this->DB->getRowSet
		('
		SELECT
			id,
			threadid
		FROM
			posts
		ORDER BY
			threadid, dat ASC
		');

	$counter = 0;
	$lastThread = 0;

	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			counter = ?
		WHERE
			id = ?
		');

	foreach ($posts as $post)
		{
		if ($lastThread != $post['threadid'])
			{
			$counter = 0;
			}

		$stm->bindInteger($counter);
		$stm->bindInteger($post['id']);
		$stm->execute();

		$counter++;

		$lastThread = $post['threadid'];
		}

	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}


private function updateThreadCounter()
	{
	$this->DB->execute('LOCK TABLES threads WRITE');

	$threads = $this->DB->getRowSet
		('
		SELECT
			id,
			forumid
		FROM
			threads
		WHERE
			forumid <> 0
		ORDER BY
			forumid, lastdate ASC
		');

	$counter = 0;
	$lastForum = 0;

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			counter = ?
		WHERE
			id = ?
		');

	foreach ($threads as $thread)
		{
		if ($lastForum != $thread['forumid'])
			{
			$counter = 0;
			}

		$stm->bindInteger($counter);
		$stm->bindInteger($thread['id']);
		$stm->execute();

		$counter++;

		$lastForum = $thread['forumid'];
		}

	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}


}

?>