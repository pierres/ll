<?php


class AdminForumsDel extends AdminForm{

private $forum = 0;
private $cat = 0;

protected function setForm()
	{
	try
		{
		$this->forum = $this->Io->getInt('forum');
		}
	catch(IoRequestException $e)
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
				forums,
				cats
			WHERE
				forum_cat.catid = cats.id
				AND forums.id = forum_cat.forumid
				AND forums.boardid = ?
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

	$this->setValue('title', 'Forum löschen');

	$this->addHidden('forum', $this->forum);
	$this->requires('forum');

	$this->addOutput('Hierdurch werden allen enthaltenen Beiträge unwiederruflich gelöscht!');

	$this->addSubmit('Forum löschen');
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	AdminFunctions::delForum($this->forum);
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}

}


?>