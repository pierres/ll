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
require('NewPost.php');

class NewThread extends NewPost{

protected $topic 		= '';
protected $poll_question 	= '';
protected $poll_options 	= '';
protected $forum 		= 0;
protected $tag			= 0;

protected $title 		= 'Neues Thema erstellen';


protected function setForm()
	{
	$this->checkInput();	// doing this here to ensure we initialize the topic if it allready exists
	try
		{
		$this->topic = $this->Input->Request->getString('topic');
		}
	catch (RequestException $e)
		{
		}
	$this->addText('topic', 'Thema', $this->topic);
	parent::setForm();
	try
		{
		$this->topic = $this->Input->Request->getString('topic');
		}
	catch (RequestException $e)
		{
		}
	$this->requires('topic');
	$this->setLength('topic', 3, 100);

	$this->setPoll();

	$this->setTag();
	}

protected function setPoll()
	{
	if ($this->User->isOnline())
		{
		if (($this->Input->Request->isValid('poll')) && !$this->Input->Request->isValid('nopoll'))
			{
			$this->addButton('nopoll', 'keine Umfrage');

			try
				{
				$this->poll_question = $this->Input->Request->getString('poll_question');
				}
			catch (RequestException $e)
				{
				}

			$this->addText('poll_question', 'Frage', $this->poll_question);
			$this->requires('poll_question');
			$this->setLength('poll_question', 3, 200);

			try
				{
				$this->poll_options = $this->Input->Request->getString('poll_options');
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
			catch (RequestException $e)
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

protected function setTag()
	{
	$tags = array(' ' => '0');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				tags
			WHERE
				boardid = ?
			');
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $tag)
			{
			$tags[$tag['name']] = $tag['id'];
			}
		$stm->close();
		$this->addRadio('tag', 'Status', $tags, $this->tag);
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->addHidden('tag', '0');
		}
	
	$this->requires('tag');
	}

protected function checkForm()
	{
	parent::checkForm();

	$this->tag = $this->Input->Request->getInt('tag');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				tags
			WHERE
				boardid = ?
				AND id = ?
			');
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->tag);

		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->tag != 0 && $this->showFailure('Ungültiger Status angegeben');
		}
	}

protected function checkInput()
	{
	if ($this->forum == 0)
		{
		try
			{
			$this->forum = $this->Input->Request->getInt('forum');
			}
		catch (RequestException $e)
			{
			$this->showFailure('Kein Forum angegeben');
			}
		}

	$this->addHidden('forum', $this->forum);
	}

protected function sendPoll()
	{
	if ($this->Input->Request->isValid('poll') && $this->User->isOnline())
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
			summary = ?,
			tag = ?'
		);
	$stm->bindString(htmlspecialchars($this->topic));
	$stm->bindInteger($this->forum);
	$stm->bindInteger($counter);
	$stm->bindString(getTextFromHtml($this->text));
	$stm->bindInteger($this->tag);

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