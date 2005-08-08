<?php

class MoveThread extends EditThread{

private $moveto 	= 0;
protected $title 	= 'Thema verschieben';


protected function setForm()
	{
	$this->setValue('title', $this->title);

	$this->allow_closed = true;

	$this->checkInput();
	$this->checkAccess($this->forum);

	$this->buildList();
	}

protected function checkForm()
	{
	try
		{
		$this->moveto = $this->Io->getInt('moveto');
		$this->checkAccess($this->moveto);
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('Wohin damit?');
		}
	}

protected function checkAccess($forum = 0)
	{
	// Wenn`s ein Moderator ist brauchen wir ja nicht weiter prüfen
	if ($this->User->isMod())
		{
		return;
		}

	parent::checkAccess();

	try
		{
		$mods = $this->Sql->fetchValue
			('
			SELECT
				mods
			FROM
				forums
			WHERE
				id = '.$forum
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Kein Forum gefunden.');
		}

	if (!$this->User->isGroup($mods))
		{
		$this->showFailure('Kein Forum gefunden.');
		}
	}

protected function buildList()
	{
	$this->addSubmit('Verschieben');

	/** FIXME: Kann man Themen aus dem Board herausschieben ? */
	try
		{
		$result = $this->Sql->fetch
			('
			SELECT
				cats.id AS catid,
				cats.name AS catname,
				forums.id,
				forums.boardid,
				forums.name,
				forums.description,
				forums.mods
			FROM
				cats,
				forums,
				forum_cat
			WHERE
				cats.boardid = '.$this->Board->getId().'
				AND forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND forums.id <> '.$this->forum.'
			ORDER BY
				cats.position,
				forum_cat.position
			');
		}
	catch (SqlNoDataException $e)
		{
		$result = array();
		}

	$cat = 0;
	$catheader = '';
	$forums = '';

	foreach ($result as $data)
		{
		if ($cat != $data['catid'])
			{
			$this->addElement('cat'.$cat,
				'<strong>&#171; '.$data['catname'].' &#187;</strong>');
			}

		$this->addElement('forum'.$data['id'],
			'<input class="radio" type="radio" name="moveto" value="'.$data['id'].'" />&nbsp;'.$data['name']);

		$cat = $data['catid'];
		}
	}

protected function sendForm()
	{
	$this->Sql->query
		('
		UPDATE
			threads
		SET
			forumid = '.$this->moveto.',
			movedfrom = '.$this->forum.'
		WHERE
			id = '.$this->thread
		);

	$this->updateForum();
	// Auch das neue Forum muß aktualisiert werden
	$this->forum = $this->moveto;
	$this->updateForum();

	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->forum);
	}

}

?>