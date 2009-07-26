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

require('pages/NewPost.php');

class NewThread extends NewPost {

protected $topic 		= '';
protected $poll_question 	= '';
protected $poll_options 	= '';
protected $forum 		= 0;


protected function setForm()
	{
	$this->title = $this->L10n->getText('Post new topic');
	$this->checkInput();	// doing this here to ensure we initialize the topic if it allready exists
	$this->topic = $this->Input->Post->getString('topic', $this->topic);
	$topicInput = new TextInputElement('topic', $this->topic, $this->L10n->getText('Topic'));
	$this->add($topicInput);
	parent::setForm();
	$this->topic = $this->Input->Post->getString('topic', $this->topic);
	$topicInput->setMinLength(3);
	$topicInput->setMaxLength(100);

	$this->setPoll();
	}

protected function setPoll()
	{
	if ($this->User->isOnline())
		{
		if (($this->Input->Post->isString('poll')) && !$this->Input->Post->isString('nopoll'))
			{
			$this->add(new ButtonElement('nopoll', $this->L10n->getText('Remove poll')));

			$this->poll_question = $this->Input->Post->getString('poll_question', '');

			$questionInput = new TextInputElement('poll_question', $this->poll_question,  $this->L10n->getText('Question'));
			$questionInput->setMinLength(3);
			$questionInput->setMaxLength(200);
			$this->add($questionInput);

			try
				{
				$this->poll_options = $this->Input->Post->getString('poll_options');
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
						$this->showWarning(sprintf($this->L10n->getText('Option %s is %d characters too long.'), $i, (strlen($poll_option)-100)));
						}
					elseif (strlen($poll_option) < 1)
						{
						$this->showWarning(sprintf($this->L10n->getText('Option %s is %d characters too long.'), $i, (1-strlen($poll_option))));
						}
					$i++;
					}

				if ($i < 3)	#FIXME Why <3?
					{
					$this->showWarning($this->L10n->getText('Please add more options'));
					}
				}
			catch (RequestException $e)
				{
				}

			$pollInput = new TextareaInputElement('poll_options', $this->poll_options, $this->L10n->getText('Options'));
			$pollInput->setRows(5);
			$this->add($pollInput);
			$this->add(new HiddenElement('poll', 1));
			}
		else
			{
			$this->add(new ButtonElement('poll', $this->L10n->getText('Add poll')));
			}
		}
	}

protected function checkInput()
	{
	if ($this->forum == 0)
		{
		try
			{
			$this->forum = $this->Input->Get->getInt('forum');
			}
		catch (RequestException $e)
			{
			$this->showFailure($this->L10n->getText('No forum specified.'));
			}
		}

	$this->setParam('forum', $this->forum);
	}

protected function sendPoll()
	{
	if ($this->Input->Post->isString('poll') && $this->User->isOnline())
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
	$stm->bindInteger($this->Input->getTime());
	$stm->bindInteger($userid);
	$stm->bindString($username);

	$stm->bindInteger($this->Input->getTime());
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
	$stm->bindInteger($this->Input->getTime());
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
	$stm->bindInteger($this->Input->getTime());
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();
	}

}

?>