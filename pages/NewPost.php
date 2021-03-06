<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class NewPost extends Form {

protected $title 	= '';
protected $text 	= '';
protected $thread	= 0;
protected $forum	= 0;
protected $counter 	= 0;


protected function setForm()
	{
	$this->checkInput();
	$this->checkAccess();

	$this->title = $this->L10n->getText('Post reply');

	$this->setTitle($this->title);

	$this->addUserMenuEntry('<a href="'.$this->Output->createUrl('MarkupHelp').'">'.$this->L10n->getText('Markup Help').'</a>');

	$this->add(new SubmitButtonElement($this->L10n->getText('Submit')));

	if (!$this->User->isOnline())
		{
		$nameInput = new TextInputElement('name', '', $this->L10n->getText('Name'));
		$nameInput->setMinLength(3);
		$nameInput->setMaxLength(25);
		$this->add($nameInput);
		}

	$textInput = new TextareaInputElement('text', $this->text, $this->L10n->getText('Message'));
	$textInput->setMinLength(3);
	$textInput->setMaxLength(65536);
	$textInput->setFocus();
	$this->add($textInput);

	$this->setFile();
	}

protected function setFile()
	{
	if ($this->User->isOnline())
		{
		if (($this->Input->Post->isString('addfile')) && !$this->Input->Post->isString('nofile'))
			{
			$this->add(new ButtonElement('nofile', $this->L10n->getText('Remove files')));
			$filesInput = new SelectInputElement('files', $this->L10n->getText('Attach files'));
			$filesInput->setMinLength(1);
			$filesInput->setMaxLength(11);
			$filesInput->setSize(10);
			$filesInput->setMultiple();
			$filesInput->setRequired(false);

			try
				{
				$stm = $this->DB->prepare
					('
					SELECT
						id,
						name
					FROM
						attachments
					WHERE
						userid = ?
					ORDER BY
						id DESC
					');
				$stm->bindInteger($this->User->getId());
				$files = $stm->getRowSet();

				foreach ($files as $file)
					{
					$filesInput->addOption($file['name'], $file['id']);
					}
				$stm->close();
				}
			catch (DBNoDataException $e)
				{
				$stm->close();
				}

			$this->add($filesInput);
			$this->add(new HiddenElement('addfile', '1'));
			}
		else
			{
			$this->add(new ButtonElement('addfile', $this->L10n->getText('Add files')));
			}
		}
	}

protected function sendFile($postid)
	{
	if($this->User->isOnline() && $this->Input->Post->isString('addfile'))
		{
		try
			{
			$files = $this->Input->Post->getArray('files');
			}
		catch (RequestException $e)
			{
			$files = array();
			}

		if (empty($files))
			{
			return;
			}
		$success = false;

		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				attachments
			WHERE
				id = ?
				AND userid = ?'
			);

		$stm2 = $this->DB->prepare
			('
			INSERT INTO
				post_attachments
			SET
				postid = ?,
				attachment_id = ?'
			);
		foreach($files as $file)
			{
			try
				{
				$stm->bindInteger($file);
				$stm->bindInteger($this->User->getId());
				$stm->getColumn();
				}
			catch (DBNoDataException $e)
				{
				continue;
				}

			$stm2->bindInteger($postid);
			$stm2->bindInteger($file);
			$stm2->execute();

			$success = true;
			}
		$stm->close();
		$stm2->close();

		if ($success)
			{
			$stm = $this->DB->prepare
				('
				UPDATE
					posts
				SET
					file = 1
				WHERE
					id = ?'
				);
			$stm->bindInteger($postid);
			$stm->execute();
			$stm->close();
			}
		}
	}

protected function checkInput()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				forumid,
				closed,
				counter
			FROM
				threads
			WHERE
				forumid != 0
				AND deleted = 0
				AND id = ?'
			);
		$stm->bindInteger($this->Input->Get->getInt('thread'));
		$data = $stm->getRow();
		$stm->close();
		}
	catch (RequestException $e)
		{
		$stm->close();
		$this->showFailure($this->L10n->getText('No topic specified'));
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure($this->L10n->getText('Topic not found'));
		}

	if ($data['closed'] != 0)
		{
		$this->showFailure($this->L10n->getText('Topic is closed'));
		}

	$this->thread = $data['id'];
	$this->forum = $data['forumid'];
	$this->counter = $data['counter'];

	$this->setParam('thread', $this->thread);
	}

protected function checkForm()
	{
	$this->text = $this->Input->Post->getString('text');

	if (!$this->User->isOnline() && !$this->Input->Post->isEmptyString('name'))
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					id,
					name
				FROM
					users
				WHERE
					name = ?'
				);
			$stm->bindString($this->Input->Post->getHtml('name'));
			$user = $stm->getRow();
			$stm->close();

			$this->showWarning(sprintf($this->L10n->getText('Name %s is already registered'), '<a href="'.$this->Output->createUrl('ShowUser', array('user' => $user['id'])).'">'.$user['name'].'</a>'));
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			}
		}

	try
		{
	$this->text = $this->Markup->toHtml($this->text);
	if ($length = strlen($this->text) > 65536)
		{
		$this->showWarning(sprintf($this->L10n->getText('Text is %d characters too long', ($length-65536))));
		}
		}
	catch (MarkupException $e)
		{
		$this->showWarning($e->getMessage());
		}
	}

protected function checkAccess()
	{
	}

protected function sendForm()
	{
	if($this->User->isOnline())
		{
		$username = $this->User->getName();
		$userid = $this->User->getId();

		$stm = $this->DB->prepare
			('
			UPDATE
				users
			SET
				posts = posts + 1,
				lastpost = ?
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Input->getTime());
		$stm->bindInteger($userid);
		$stm->execute();
		$stm->close();
		}
	else
		{
// 		if (!$this->Input->Post->isEmptyString('name'))
// 			{
			$username = $this->Input->Post->getHtml('name');
// 			}
// 		else
// 			{
// 			$username = 'Gast';
// 			}

		$userid = 0;
		}

 	$this->DB->execute('LOCK TABLES posts WRITE');

	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			posts
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($this->thread);
	$counter = $stm->getColumn();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			posts
		SET
			threadid = ?,
			userid = ?,
			username = ?,
			text = ?,
			dat = ?,
			counter = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($userid);
	$stm->bindString($username);
	$stm->bindString($this->text);
	$stm->bindInteger($this->Input->getTime());

	$stm->bindInteger($counter);

	$stm->execute();
	$stm->close();

	$insertid = $this->DB->getInsertId();

 	$this->DB->execute('UNLOCK TABLES');

	$this->sendFile($insertid);

	$this->updateThread($userid, $username);
	$this->updateForum($userid);
	$this->updateBoard();

	$this->Log->insert($this->thread, $this->Input->getTime());
	$this->Log->collectGarbage();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('Postings', array('thread' => $this->thread, 'post' => -1));
	}

protected function updateThread($userid, $username)
	{
 	$this->DB->execute('LOCK TABLES threads WRITE');

	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)-1
		FROM
			threads
		WHERE
			forumid = ?
		');
	$stm->bindInteger($this->forum);
	$newCounter = $stm->getColumn();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			counter = counter - 1
		WHERE
			forumid = ?
			AND counter > ?
		');
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->counter);

	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			counter = ?,
			lastdate = ?,
			lastuserid = ?,
			lastusername = ?,
			posts = posts + 1
		WHERE
			id = ?
		');
	$stm->bindInteger($newCounter);
	$stm->bindInteger($this->Input->getTime());
	$stm->bindInteger($userid);
	$stm->bindString($username);
	$stm->bindInteger($this->thread);

	$stm->execute();
	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
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
			posts = posts + 1
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