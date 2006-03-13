<?php


class AdminForumsEx extends AdminForm{


private $cat = 0;


protected function setForm()
	{
	try
		{
		$this->cat = $this->Io->getInt('cat');
		}
	catch (IoRequestException $e)
		{
		$this->Io->redirect('AdminCats');
		}

	$this->setValue('title', 'Externe Foren hinzufügen');
	$this->addSubmit('Hinzufügen');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				forums.id,
				forums.name,
				forums.boardid,
				boards.name AS boardname
			FROM
				forums,
				boards
			WHERE
				forums.boardid != ?
				AND forums.boardid = boards.id
				AND forums.id NOT IN
					(
					SELECT
						forum_cat.forumid
					FROM
						forum_cat,
						cats
					WHERE
						forum_cat.catid = cats.id
						AND cats.boardid = ?
					)
			ORDER BY
				forums.boardid,
				forums.id
			');
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->Board->getId());

		$board = 0;
		foreach ($stm->getRowSet() as $forum)
			{
			if ($board != $forum['boardid'])
				{
				$this->addOutput('<div style="margin:8px"><strong>&#171; '.$forum['boardname'].' &#187;</strong></div>');
				}
			$board = $forum['boardid'];

			$this->addOutput
				('
				<input type="checkbox" name="forums['.$forum['id'].']" value="1" />'.$forum['name'].'<br />
				');
			}
		}
	catch (DBNoDataException $e)
		{
		}

	$this->addHidden('cat', $this->cat);
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
				id = ?
				AND boardid = ?'
			);
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());
		$stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$this->Io->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				COUNT(*)+1
			FROM
				forum_cat
			WHERE
				catid = ?'
			);
		$stm->bindInteger($this->cat);
		$position = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$this->redirect();
		}

	/** FIXME */
	foreach($this->Io->getArray() as $forum => $value)
		{
		try
			{
			$stm = $this->DB->prepare
				('
				INSERT INTO
					forum_cat
				SET
					forumid = ?,
					catid = ?,
					position = ?'
				);
			$stm->bindInteger($forum);
			$stm->bindInteger($this->cat);
			$stm->bindInteger($position);
			$stm->execute();
			}
		catch (DBNoDataException $e)
			{
			}
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}


}

?>