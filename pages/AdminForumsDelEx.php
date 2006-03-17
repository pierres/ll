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
		$tsm = $this->DB->prepare
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
				AND forums.boardid != ?
				AND cats.boardid = ?
				AND forum_cat.forumid = ?'
			);
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->forum);
		$this->cat = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$this->Io->redirect('AdminCats');
		}

	$stm = $this->DB->prepare
		('
		DELETE FROM
			forum_cat
		WHERE
			forumid = ?
			AND catid = ?'
		);
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->cat);
	$stm->execute();
	}

public function show()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}

}


?>