<?php


class AdminForumsDelEx extends AdminPage{

private $cat = 0;
private $forum = 0;


public function prepare()
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
				forums,
				cats
			WHERE
				forum_cat.catid = cats.id
				AND forums.id = forum_cat.forumid
				AND forums.boardid != '.$this->Board->getId().'
				AND cats.boardid = '.$this->Board->getId().'
				AND forum_cat.forumid = '.$this->forum
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->Io->redirect('AdminCats');
		}

	$this->Sql->query
		('
		DELETE FROM
			forum_cat
		WHERE
			forumid = '.$this->forum.'
			AND catid = '.$this->cat
		);
	}

public function show()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}

}


?>