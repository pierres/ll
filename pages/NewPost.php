<?php

/** TODO: Sicherheit und Zugriffsrechte */


class NewPost extends Form {


protected $text 	= '';
protected $thread	= 0;
protected $forum	= 0;

protected $time 	= 0;
protected $smilies 	= true;
protected $title 	= 'Beitrag schreiben';


protected function setForm()
	{
	$this->checkInput();
	$this->checkAccess();

	$this->setValue('title', $this->title);
	$this->time = time();

	$this->addSubmit('Abschicken');
	$this->addButton('preview', 'Vorschau');


	if ($this->Io->isRequest('preview') && !$this->Io->isEmpty('text'))
		{
		$this->text = $this->Io->getString('text');
		$this->Markup->enableSmilies($this->Io->isRequest('smilies'));

		$this->addElement('previewwindow',
		'<div class="preview">'.$this->Markup->toHtml($this->text).'</div>');
		}

	if (!$this->User->isOnline())
		{
		$this->addText('name', 'Dein Name');
		$this->setLength('name', 3,25);
		}

	$this->addTextarea('text', 'Deine Nachricht', $this->text);
	$this->requires('text');
	$this->setLength('text', 3, 65536);

	$this->addCheckbox('smilies', 'grafische Smilies', $this->smilies);
	}

protected function checkInput()
	{
	try
		{
		$data = $this->Sql->fetchRow
			('
			SELECT
				id,
				forumid,
				closed,
				deleted
			FROM
				threads
			WHERE
				forumid <> 0
				AND id = '.$this->Io->getInt('thread')
			);
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Thema nicht gefunden!');
		}

	if ($data['deleted'] != 0)
		{
		$this->showFailure('Thema wurde gelöscht!');
		}

	if ($data['closed'] != 0)
		{
		$this->showFailure('Thema wurde geschlossen!');
		}

	$this->thread = $data['id'];
	$this->forum = $data['forumid'];

	$this->addHidden('thread', $this->thread);
	}

protected function checkForm()
	{
	$this->smilies = $this->Io->isRequest('smilies');
	$this->text = $this->Io->getString('text');
	}

protected function checkAccess()
	{
	/** Privater Thread -> Prüfung */
	}

protected function sendForm()
	{
	if($this->User->isOnline())
		{
		$username = $this->User->getName();
		$userid = $this->User->getId();

		$this->Sql->query(
			'
			UPDATE
				users
			SET
				posts = posts + 1,
				lastpost = '.$this->time.'
			WHERE
				id = '.$userid
			);
		}
	else
		{
		if (!$this->Io->isEmpty('name'))
			{
			$username = $this->Io->getString('name');
			}
		else
			{
			$username = 'Gast';
			}

		$userid = 0;
		}

	$this->Markup->enableSmilies($this->smilies);
	$this->text = $this->Markup->toHtml($this->text);

	$this->Sql->query
		('
		INSERT INTO
			posts
		SET
			threadid = '.$this->thread.',
			userid = '.$userid.',
			username = \''.$this->Sql->formatString($username).'\',
			text = \''.$this->Sql->escapeString($this->text).'\',
			dat = '.$this->time.',
			smilies ='.($this->smilies ? 1 : 0)
		);

	$this->UpdateThread();
	$this->updateForum();

	$this->Sql->query(
		'
		UPDATE
			boards
		SET
			posts = posts + 1,
			lastpost = '.$this->time.'
		WHERE
			id = '.$this->Board->getId()
		);

	$this->Log->insert($this->thread, $this->time);

	$this->redirect();
	}

protected function updateThread()
	{
	AdminFunctions::updateThread($this->thread);
	}

protected function updateForum()
	{
	AdminFunctions::updateForum($this->forum);
	}

protected function redirect()
	{
	try
		{
		$data = $this->Sql->fetchRow
			('
			SELECT
				threads.name AS thread,
				forums.name AS forum,
				forums.id AS forumid
			FROM
				threads,
				forums
			WHERE
				threads.id = '.$this->thread.'
				AND threads.forumid = forums.id
			');
		}
	catch (SqlNoDataException $e)
		{
		$data['thread'] = '';
		$data['forum'] = '';
		$data['forumid'] = 0;
		}

	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Beitrag geschrieben
				</td>
			</tr>
			<tr>
				<td class="main">
					Wohin darf es nun gehen?
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=Postings;id='.$this->Board->getId().';thread='.$this->thread.';post=-1#last">&#187; zurück zum Thema &quot;<em>'.$data['thread'].'</em>&quot;</a>
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=Threads;id='.$this->Board->getId().';forum='.$data['forumid'].'">&#187; zum Forum &quot;<em>'.$data['forum'].'</em>&quot;</a>
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=Forums;id='.$this->Board->getId().'">&#187; zum Board &quot;<em>'.$this->Board->getName().'</em>&quot;</a>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Beitrag geschrieben');
	$this->setValue('body', $body);
	}

}


?>