<?php

class MarkAllAsRead extends Page{


public function prepare()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$threads = $this->Sql->fetch
				('
				SELECT
					id,
					lastdate
				FROM
					threads
				WHERE
					forumid != 0
					AND lastdate > '.(time() - (86400 * $this->Settings->getValue('log_timeout')))
				);
			}
		catch (SqlNoDataException $e)
			{
			$threads = array();
			}

		foreach ($threads as $thread)
			{
			$this->Log->insert($thread['id'], $thread['lastdate']);
			}
		}
	}

public function show()
	{
	$this->Io->redirect('Forums');
	}

}

?>