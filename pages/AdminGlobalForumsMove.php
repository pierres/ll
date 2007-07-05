<?php

class AdminGlobalForumsMove extends AdminForm{

private $cat = 0;
private $forum = 0;

protected function setForm()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	try
		{
		$this->forum = $this->Io->getInt('forum');
		}
	catch (IoRequestException $e)
		{
		$this->Io->redirect('AdminCats');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				cats.id
			FROM
				forum_cat,
				cats
			WHERE
				forum_cat.catid = cats.id
				AND forum_cat.forumid = ?'
			);
		$stm->bindInteger($this->forum);
		$this->cat = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Io->redirect('AdminCats');
		}

	$this->setValue('title', 'Forum verschieben');

	$this->addSubmit('Verschieben');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				(SELECT name FROM boards WHERE id = cats.boardid) AS board
			FROM
				cats
			WHERE
				id != ?
				AND cats.boardid <> ?
			ORDER BY board ASC
			');
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $cat)
			{
			$this->addOutput
				('
				<input type="radio" name="newcat" value="'.$cat['id'].'" /><strong>'.$cat['board'].'</strong> '.$cat['name'].'
				<br />
				');
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->addHidden('forum', $this->forum);
	}

protected function checkForm()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				cats
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Io->getInt('newcat'));
		$stm->getColumn();
		$stm->close();
		}
	catch(DBNoDataException $e)
		{
		$stm->close();
		$this->Io->redirect('AdminCats');
		}
	catch(IoRequestException $e)
		{
		$stm->close();
		$this->Io->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	/** FIXME: We should remove all links to this forum existing in the target category */
	$stm = $this->DB->prepare
		('
		UPDATE
			forum_cat
		SET
			catid = ?
		WHERE
			catid = ?
			AND forumid = ?'
		);
	$stm->bindInteger($this->Io->getInt('newcat'));
	$stm->bindInteger($this->cat);
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			forums
		SET
			boardid = (SELECT boardid FROM cats WHERE id = ?)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Io->getInt('newcat'));
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->Io->getInt('newcat'));
	}


}


?>