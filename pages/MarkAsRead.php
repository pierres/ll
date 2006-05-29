<?php

class MarkAsRead extends Page{


public function prepare()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$forum = $this->Io->getInt('forum');
			}
		catch (IoRequestException $e)
			{
			return;
			}

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
					forumid = ?
					AND forumid != 0
					AND lastdate > ?'
				);
			$stm->bindInteger($forum);
			$stm->bindInteger(time() - (86400 *  $this->Settings->getValue('log_timeout')));

			foreach ($stm->getRowSet() as $thread)
				{
				$this->Log->insert($thread['id'], $thread['lastdate']);
				}
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			}
		}
	}

public function show()
	{
	$this->Io->redirect('Forums');
	}

}

?>