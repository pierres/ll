<?php

class AdminForumsMove extends AdminForm{

private $cat = 0;
private $forum = 0;

protected function setForm()
	{
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
				AND cats.boardid = ?
				AND forum_cat.forumid = ?'
			);
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->forum);
		$this->cat = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
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
				name
			FROM
				cats
			WHERE
				id != ?
				AND boardid = ?
			');
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());

		foreach ($stm->getRowSet() as $cat)
			{
			$this->addOutput
				('
				<input type="radio" name="newcat" value="'.$cat['id'].'" />'.$cat['name'].'
				<br />
				');
			}
		}
	catch (DBNoDataException $e)
		{
		}

	$this->addHidden('forum', $this->forum);
	}

protected function checkForm()
	{
	try
		{
		/** FIXME: Nachprüfen, ob wirklich sicher; es sollte reichen, wenn die Kategorie getestet wird */
		/*
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				forums
			WHERE
				id = ?
				AND boardid = ?'
			);
		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->Board->getId());
		$stm->getColumn();
		*/

		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				cats
			WHERE
				id = ?
				AND boardid = ?'
			);
		$stm->bindInteger($this->Io->getInt('newcat'));
		$stm->bindInteger($this->Board->getId());
		$stm->getColumn();
		}
	catch(DBNoDataException $e)
		{
		$this->Io->redirect('AdminCats');
		}
	catch(IoRequestException $e)
		{
		$this->Io->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
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

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->Io->getInt('newcat'));
	}


}


?>