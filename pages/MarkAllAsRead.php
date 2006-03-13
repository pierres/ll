<?php

class MarkAllAsRead extends Page{


public function prepare()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					id,
					lastdate
				FROM
					threads
				WHERE
					forumid != 0
					AND lastdate > ?'
				);
			$stm->bindInteger(time() - (86400 * $this->Settings->getValue('log_timeout')));

			foreach ($stm->getRowSet() as $thread)
				{
				$this->Log->insert($thread['id'], $thread['lastdate']);
				}
			}
		catch (DBNoDataException $e)
			{
			}
		}
	}

public function show()
	{
	$this->Io->redirect('Forums');
	}

}

?>