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
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id
			FROM
				threads,
				thread_user
			WHERE
				threads.forumid = 0
				AND thread_user.threadid = threads.id
				AND thread_user.userid = ?
				AND threads.id = ?'
			);
		$stm->bindInteger($this->User->getId());
		$stm->bindInteger($this->Io->getInt('thread'));
		$this->thread = $stm->getColumn();
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$this->showFailure('Thema nicht gefunden!');
		}

	$this->addHidden('thread', $this->thread);
	}

protected function checkAccess()
	{
	/** Privater Thread -> Prüfung */
	}

protected function sendForm()
	{
	$this->Markup->enableSmilies($this->smilies);
	$this->text = $this->Markup->toHtml($this->text);
	// BugFix for Bug#1
	if ($length = strlen($this->text) > 65536)
		{
		$this->showFailure('Der Text ist '.($length-65536).' Zeichen zu lang!');
		}

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
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->User->getId());
	$stm->execute();

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
			smilies = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->User->getId());
	$stm->bindString($this->User->getName());
	$stm->bindString($this->text);
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->smilies ? 1 : 0);

	$stm->execute();

	$this->sendFile($this->DB->getInsertId());

	$this->UpdateThread();
	//$this->updateForum();

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
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();

	$this->Log->insert($this->thread, $this->time);

	$this->redirect();
	}

protected function updateThread()
	{
	AdminFunctions::updateThread($this->thread);
	}

protected function updateForum()
	{
	//AdminFunctions::updateForum($this->forum);
	}

protected function redirect()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				name
			FROM
				threads
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->thread);
		$threadName = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$threadName = '';
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
					<a href="?page=PrivatePostings;id='.$this->Board->getId().';thread='.$this->thread.';post=-1#last">&#187; zurück zum Thema &quot;<em>'.$threadName.'</em>&quot;</a>
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