<?php


class NewThread extends NewPost{

protected $topic 		= '';
protected $poll_question 	= '';
protected $poll_options 	= '';
protected $forum 		= 0;

protected $title 		= 'Neues Thema erstellen';


protected function setForm()
	{
	/** FIXME: Das machen wir zweimal um das Thema opben zu haben...sollte man in Form beheben */
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
	/** Hier ein Problem mit privatebn Threads */
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
		$this->Sql->query
			('
			INSERT INTO
				polls
			SET
				id = '.$this->thread.',
				question = \''.$this->Sql->formatString($this->poll_question).'\'
			');

		foreach ($poll_options as $option)
			{
			$option = trim($option);

			if(!empty($option))
				{
				$this->Sql->query
					('
					INSERT INTO
						poll_values
					SET
						pollid = '.$this->thread.',
						value = \''.$this->Sql->formatString($option).'\'
					');
				}
			}

		$this->Sql->query
			('
			UPDATE
				threads
			SET
				poll = 1
			WHERE
				id = '.$this->thread
			);
		}
	}

protected function sendForm()
	{
	$this->Sql->query(
		'
		INSERT INTO
			threads
		SET
			name = \''.$this->Sql->formatString($this->topic).'\',
			forumid = '.$this->forum.',
			lastuserid = '.$this->User->getId().',
			lastusername =  \''.$this->Sql->formatString($this->User->getName()).'\',
			lastdate = '.$this->time
		);

	$this->thread = $this->Sql->insertId();

	$this->Sql->query(
		'
		UPDATE
			boards
		SET
			threads = threads + 1
		WHERE
			id = '.$this->Board->getId()
		);

	$this->sendPoll();

	parent::sendForm();
	}
}

?>