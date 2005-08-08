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

	$forums = $this->Sql->fetch
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
			forums.boardid != '.$this->Board->getId().'
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
					AND cats.boardid = '.$this->Board->getId().'
				)
		ORDER BY
			forums.boardid,
			forums.id
		');
	$board = 0;
	foreach ($forums as $forum)
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

	$this->addHidden('cat', $this->cat);
	}

protected function checkForm()
	{
	try
		{
		$this->Sql->fetchValue
			('
			SELECT
				id
			FROM
				cats
			WHERE
				id = '.$this->cat.'
				AND boardid = '.$this->Board->getId()
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->Io->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	$forums = $this->Io->getArray();

	foreach($forums as $forum => $value)
		{
		$position = $this->Sql->fetchValue('SELECT MAX(position)+1 FROM forum_cat WHERE catid = '.$this->cat);

		$this->Sql->query
			('
			INSERT INTO
				forum_cat
			SET
				forumid = '.intval($forum).',
				catid = '.$this->cat.',
				position = '.$position
			);
		}

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}


}

?>