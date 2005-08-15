<?php


class NewPrivateThread extends NewPrivatePost{

protected $topic 		= '';
protected $recipients 		= '';
protected $tousers		= array();
protected $poll_question 	= '';
protected $poll_options 	= '';

protected $title 		= 'Neues Thema erstellen';


protected function setForm()
	{
	/** FIXME: Das machen wir zweimal um das Thema oben zu haben...sollte man in Form beheben */

	$this->addRecipients();

	try
		{
		$this->topic = $this->Io->getString('topic');
		}
	catch (IoException $e)
		{
		}
	$this->addText('topic', 'Thema', $this->topic);

	parent::setForm();

	$this->addRecipients();

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

protected function addRecipients()
	{
	try
		{
		$this->recipients = $this->Io->getString('recipients');
		}
	catch (IoException $e)
		{
		}
	$this->addText('recipients', 'Empfänger');
	$this->requires('recipients');
	}

protected function setPoll()
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

protected function checkInput()
	{
	}

protected function checkForm()
	{
	parent::checkForm();

	$this->checkRecipients();
	}

protected function checkRecipients()
	{
	$this->tousers[] = $this->User->getId();

	$recipients = array_map('trim', explode(',', $this->recipients));

	try
		{
		foreach ($recipients as $recipient)
			{
			$user = AdminFunctions::getUserId($recipient);
			if (!in_array($user, $this->tousers) && $user != $this->User->getId())
				{
				$this->tousers[] = $user;
				}
			}
		}
	catch(SqlNoDataException $e)
		{
		$this->showWarning('Empfänger "'.htmlspecialchars($recipient).'" ist unbekannt.');
		}
	}

protected function sendPoll()
	{
	if ($this->Io->isRequest('poll'))
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

	foreach ($this->tousers as $user)
		{
		$this->Sql->query
			('
			INSERT INTO
				thread_user
			SET
				threadid = '.$this->thread.',
				userid = '.$user
			);
		}

	$this->sendPoll();

	parent::sendForm();
	}
}

?>