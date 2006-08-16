<?php

class MoveThread extends EditThread{

private $moveto 	= 0;
protected $title 	= 'Thema verschieben';


protected function setForm()
	{
	$this->setValue('title', $this->title);

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
	if ($this->User->isForumMod($forum))
		{
		return;
		}

	parent::checkAccess();
	}

protected function buildList()
	{
	$this->addSubmit('Verschieben');

	try
		{
		$stm = $this->DB->prepare
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
				cats.boardid = ?
				AND forum_cat.forumid = forums.id
				AND forum_cat.catid = cats.id
				AND forums.id <> ?
			ORDER BY
				cats.position,
				forum_cat.position
			');
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->forum);

		$cat = 0;
		$catheader = '';
		$forums = '';

		foreach ($stm->getRowSet() as $data)
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

		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			forumid = ?,
			movedfrom = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->moveto);
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->thread);
	$stm->execute();
	$stm->close();

	AdminFunctions::updateForum($this->forum);
	// Auch das neue Forum muß aktualisiert werden
	AdminFunctions::updateForum($this->moveto);

	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('Threads', 'forum='.$this->moveto);
	}

}

?>