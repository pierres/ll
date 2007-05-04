<?php

/** FIXME: Nicht geschützt via Form */
class SubmitPoll extends Page{

private $id 		= 0;
private $target 	= '';


public function prepare()
	{
	$this->target = ($this->Io->isRequest('target') ? $this->Io->getString('target') : 'Postings');

	try
		{
		$this->id = $this->Io->getInt('thread');
		}
	catch (IoException $e)
		{
		$this->showWarning('Kein Thema angegeben.');
		}
	
	if ($this->Io->isRequest('result'))
		{
		$this->reload();
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forumid
			FROM
				threads
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$forum = $stm->getColumn();
		$stm->close();

		if ($forum == 0)
			{
			$stm = $this->DB->prepare
				('
				SELECT
					userid
				FROM
					thread_user
				WHERE
					userid = ?
					AND threadid = ?'
				);
			$stm->bindInteger($this->User->getId());
			$stm->bindInteger($this->id);
			$stm->getColumn();
			$stm->close();
			}

		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				polls
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->id);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$this->Io->setStatus(Io::NOT_FOUND);
		$this->setValue('meta.robots', 'noindex,nofollow');
		$this->showWarning('Keine Umfrage gefunden.');
		}
	}

protected function reload()
	{
	$this->Io->redirect($this->target, 'thread='.$this->id.($this->Io->isRequest('result') ? ';result' : ''));
	}

public function show()
	{
	if ($this->hasVoted())
		{
		$this->reload();
		}

	try
		{
		$valueid = $this->Io->getInt('valueid');
		}
	catch (IoException $e)
		{
		$this->reload();
		}

	$stm = $this->DB->prepare
		('
		INSERT INTO
			poll_voters
		SET
			pollid = ?,
			userid = ?'
		);
	$stm->bindInteger($this->id);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			poll_values
		SET
			votes = votes + 1
		WHERE
			id = ?
			AND pollid = ?'
		);
	$stm->bindInteger($valueid);
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();

	$this->reload();
	}

private function hasVoted()
	{
	if (!$this->User->isOnline())
		{
		return true;
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				userid
			FROM
				poll_voters
			WHERE
				pollid = ?
				AND userid = ?'
			);
		$stm->bindInteger($this->id);
		$stm->bindInteger($this->User->getId());
		$stm->getColumn();
		$stm->close();
		return true;
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return false;
		}
	}

}

?>