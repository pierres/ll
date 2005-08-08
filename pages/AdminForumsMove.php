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
		$this->cat = $this->Sql->fetchValue
			('
			SELECT
				cats.id
			FROM
				forum_cat,
				cats
			WHERE
				forum_cat.catid = cats.id
				AND cats.boardid = '.$this->Board->getId().'
				AND forum_cat.forumid = '.$this->forum
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->Io->redirect('AdminCats');
		}

	$this->setValue('title', 'Forum verschieben');

	$this->addSubmit('Verschieben');

	try
		{
		$cats = $this->Sql->fetch
			('
			SELECT
				id,
				name
			FROM
				cats
			WHERE
				id != '.$this->cat.'
				AND boardid = '.$this->Board->getId().'
			');
		}
	catch (SqlNoDataException $e)
		{
		$cats = array();
		}

	foreach ($cats as $cat)
		{
		$this->addOutput
			('
			<input type="radio" name="newcat" value="'.$cat['id'].'" />'.$cat['name'].'
			<br />
			');
		}

	$this->addHidden('forum', $this->forum);
	}

protected function checkForm()
	{
	try
		{
		/** FIXME: Nachprüfen, ob wirklich sicher; es sollte reichen, wenn die Kategorie getestet wird */
		/*
		$this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				forums
			WHERE
				id = '.$this->forum.'
				AND boardid = '.$this->Board->getId()
			);
		*/

		$this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				cats
			WHERE
				id = '.$this->Io->getInt('newcat').'
				AND boardid = '.$this->Board->getId()
			);
		}
	catch(SqlNoDataException $e)
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
	$this->Sql->query
		('
		UPDATE
			forum_cat
		SET
			catid = '.$this->Io->getInt('newcat').'
		WHERE
			catid = '.$this->cat.'
			AND forumid = '.$this->forum
		);

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->Io->getInt('newcat'));
	}


}


?>