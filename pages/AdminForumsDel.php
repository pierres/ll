<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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