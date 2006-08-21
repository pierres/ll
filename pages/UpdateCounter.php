<?php

class UpdateCounter extends Page{


public function prepare()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showWarning('Zutritt verboten!');
		}

	$this->updatePostCounter();
	$this->updateThreadCounter();

	$this->setValue('body', 'fertig');
	}

private function updatePostCounter()
	{
	$threads = $this->DB->getColumnSet
		('
		SELECT
			id
		FROM
			threads
		');

	foreach ($threads as $thread)
		{
		AdminFunctions::updateThread($thread);
		}
	}


private function updateThreadCounter()
	{
	$forums = $this->DB->getColumnSet
		('
		SELECT
			id
		FROM
			forums
		WHERE
			id <> 0
		');

	foreach ($forums as $forum)
		{
		AdminFunctions::updateForum($forum);
		}
	}


}

?>