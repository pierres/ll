<?php

/** TODO: Sicherheit und Zugriffsrechte */


class NewPrivatePost extends NewPost {

public function __construct()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder');
		}

	parent::__construct();
	}


protected function checkInput()
	{
	try
		{
		$this->thread = $this->Sql->fetchValue
			('
			SELECT
				threads.id
			FROM
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = '.$this->User->getId().'
				AND threads.id = '.$this->Io->getInt('thread')
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
	$this->Sql->query(
		'
		UPDATE
			users
		SET
			posts = posts + 1,
			lastpost = '.$this->time.'
		WHERE
			id = '.$this->User->getId()
		);

	$this->Markup->enableSmilies($this->smilies);
	$this->text = $this->Markup->toHtml($this->text);

	$this->Sql->query
		('
		INSERT INTO
			posts
		SET
			threadid = '.$this->thread.',
			userid = '.$this->User->getId().',
			username = \''.$this->Sql->escapeString($this->User->getName()).'\',
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
				name AS thread
			FROM
				threads
			WHERE
				id = '.$this->thread
			);
		}
	catch (SqlNoDataException $e)
		{
		$data['thread'] = '';
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
					<a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$this->thread.';post=-1#last">&#187; zurück zum Thema &quot;<em>'.$data['thread'].'</em>&quot;</a>
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=PrivateThreads;id='.$this->Board->getId().'">&#187; zurück zu den &quot;<em>Privaten Themen</em>&quot;</a>
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