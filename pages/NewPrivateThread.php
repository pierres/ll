<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
require('NewPrivatePost.php');

class NewPrivateThread extends NewPrivatePost{

protected $topic 		= '';
protected $recipients 		= '';
protected $tousers		= array();
protected $poll_question 	= '';
protected $poll_options 	= '';

protected $title 		= 'Neues Thema erstellen';


protected function setForm()
	{
	$this->checkInput();	// doing this here to ensure we initialize the topic if it allready exists

	try
		{
		$this->topic = $this->Io->getString('topic');
		}
	catch (IoException $e)
		{
		}
	$this->addText('topic', 'Thema', $this->topic);

	$this->addRecipients();
	parent::setForm();

	try
		{
		$this->topic = $this->Io->getString('topic');
		}
	catch (IoException $e)
		{
		}
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
			summary = ?'
		);
	$stm->bindString(htmlspecialchars($this->topic));
	$stm->bindString(getTextFromHtml($this->text));
	$stm->execute();
	$stm->close();

	$this->thread = $this->DB->getInsertId();

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