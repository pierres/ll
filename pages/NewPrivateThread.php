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
	catch(DBNoDataException $e)
		{
		$this->showWarning('Empfänger "'.htmlspecialchars($recipient).'" ist unbekannt.');
		}

	if (count($this->tousers) < 2)
		{
		$this->showWarning('keine Empfänger angegeben.');
		}
	}

protected function sendPoll()
	{
	if ($this->Io->isRequest('poll'))
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
	$stm = $this->DB->prepare
		('
		INSERT INTO
			threads
		SET
			name = ?,
			counter = 0'
		);
	$stm->bindString(htmlspecialchars($this->topic));
	$stm->execute();
	$stm->close();

	$this->thread = $this->DB->getInsertId();

	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			threads = threads + 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			thread_user
		SET
			threadid = ?,
			userid = ?'
		);
	foreach ($this->tousers as $user)
		{
		$stm->bindInteger($this->thread);
		$stm->bindInteger($user);
		$stm->execute();
		}
	$stm->close();

	$this->sendPoll();
	$this->sendThreadSummary();

	parent::sendForm();
	}

protected function sendThreadSummary()
	{
	$summary = str_replace('<br />', ' ', $this->text);
	$summary = str_replace("\n", ' ', strip_tags($summary));
	$summary = cutString($summary,  300);

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			summary = ?
		WHERE
			id = ?
		');

	$stm->bindString($summary);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();
	}

}

?>