<?php

require('NewPost.php');

class NewThread extends NewPost{

protected $topic 		= '';
protected $poll_question 	= '';
protected $poll_options 	= '';
protected $forum 		= 0;

protected $title 		= 'Neues Thema erstellen';


protected function setForm()
	{
	/** FIXME: Das machen wir zweimal um das Thema oben zu haben...sollte man in Form beheben */
	try
		{
		$this->topic = $this->Io->getString('topic');
		}
	catch (IoException $e)
		{
		}
	$this->addText('topic', 'Thema', $this->topic);

	parent::setForm();

	try
		{
		$this->topic = $this->Io->getString('topic');
		}
	catch (IoException $e)
		{
		}

	$this->addText('topic', 'Thema', $this->topic);
	$this->requires('topic');
	$this->setLength('topic', 3, 100);

	$this->setPoll();
	}

protected function setPoll()
	{
	if ($this->User->isOnline())
		{
		if (($this->Io->isRequest('poll')) && !$this->Io->isRequest('nopoll'))
			{
			$this->addButton('nopoll', 'keine Umfrage');

			try
				{
				$this->poll_question = $this->Io->getString('poll_question');
				}
			catch (IoRequestException $e)
				{
				}

			$this->addText('poll_question', 'Frage', $this->poll_question);
			$this->requires('poll_question');
			$this->setLength('poll_question', 3, 200);

			try
				{
				$this->poll_options = $this->Io->getString('poll_options');
				$poll_options = explode("\n", $this->poll_options);
				$i = 1;
				foreach($poll_options as $poll_option)
					{
					$poll_option = trim($poll_option);
					if (empty($poll_option))
						{
						continue;
						}

					if (strlen($poll_option) > 100)
						{
						$this->showWarning('Antwort '.$i.' ist '.(strlen($poll_option)-100).' Zeichen zu lang.');
						}
					elseif (strlen($poll_option) < 1)
						{
						$this->showWarning('Antwort '.$i.' ist '.(1-strlen($poll_option)).' Zeichen zu kurz.');
						}
					$i++;
					}

				if ($i < 3)
					{
					$this->showWarning('Sind das zu wenige oder zu wenige Antwortmöglichkeiten?');
					}
				}
			catch (IoRequestException $e)
				{
				}

			$this->addTextarea('poll_options', 'Antworten', $this->poll_options, 80, 5);
			$this->addHidden('poll', 1);

			}
		else
			{
			$this->addButton('poll', 'Umfrage');
			}
		}
	}

protected function checkInput()
	{
	if ($this->forum == 0)
		{
		try
			{
			$this->forum = $this->Io->getInt('forum');
			}
		catch (IoException $e)
			{
			$this->showFailure('Kein Forum angegeben');
			}
		}

	$this->addHidden('forum', $this->forum);
	}

protected function sendPoll()
	{
	if ($this->Io->isRequest('poll') && $this->User->isOnline())
		{
		$poll_options = explode("\n",$this->poll_options);
		$stm = $this->DB->prepare
			('
			INSERT INTO
				polls
			SET
				id = ?,
				question = ?
			');
		$stm->bindInteger($this->thread);
		$stm->bindString(htmlspecialchars($this->poll_question));
		$stm->execute();
		$stm->close();

		$stm = $this->DB->prepare
			('
			INSERT INTO
				poll_values
			SET
				pollid = ?,
				value = ?
			');
		foreach ($poll_options as $option)
			{
			$option = trim($option);

			if(!empty($option))
				{
				$stm->bindInteger($this->thread);
				$stm->bindString(htmlspecialchars($option));
				$stm->execute();
				}
			}
		$stm->close();

		$stm = $this->DB->prepare
			('
			UPDATE
				threads
			SET
				poll = 1
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->thread);
		$stm->execute();
		$stm->close();
		}
	}

protected function sendForm()
	{
	$this->DB->execute('LOCK TABLES threads WRITE');

	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			threads
		WHERE
			forumid = ?'
		);
	$stm->bindInteger($this->forum);
	$counter = $stm->getColumn();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			threads
		SET
			name = ?,
			forumid = ?,
			counter = ?,
			summary = ?'
		);
	$stm->bindString(htmlspecialchars($this->topic));
	$stm->bindInteger($this->forum);
	$stm->bindInteger($counter);

	$stm->bindString(getTextFromHtml($this->text));

	$stm->execute();
	$stm->close();

	$this->thread = $this->DB->getInsertId();

	$this->DB->execute('UNLOCK TABLES');

	$this->sendPoll();

	parent::sendForm();
	}

protected function updateThread($userid, $username)
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			firstdate = ?,
			firstuserid = ?,
			firstusername = ?,
			lastdate = ?,
			lastuserid = ?,
			lastusername = ?,
			posts = 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->time);
	$stm->bindInteger($userid);
	$stm->bindString($username);

	$stm->bindInteger($this->time);
	$stm->bindInteger($userid);
	$stm->bindString($username);

	$stm->bindInteger($this->thread);

	$stm->execute();
	$stm->close();
	}

protected function updateForum($userid)
	{
 	$stm = $this->DB->prepare
		('
		UPDATE
			forums
		SET
			lastthread = ?,
			lastdate = ?,
			lastposter = ?,
			posts = posts + 1,
			threads = threads + 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->time);
	$stm->bindInteger($userid);
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();
	}

protected function updateBoard()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			posts = posts + 1,
			threads = threads + 1,
			lastpost = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();
	}

}

?>