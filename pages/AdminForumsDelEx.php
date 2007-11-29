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
class AdminForumsDelEx extends AdminForm{

private $cat = 0;
private $forum = 0;


public function setForm()
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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Io->redirect('AdminCats');
		}

	$this->setValue('title', 'Externes Forum löschen');

	$this->addHidden('forum', $this->forum);
	$this->requires('forum');

	$this->addOutput('Hierdurch wird der Verweis auf das externe Forum aus dem Board entfernt. Dabei werden keine Beiträge gelöscht.');

	$this->addSubmit('Externes Forum löschen');
	}

protected function checkForm()
	{
	}

public function sendForm()
	{
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
	$stm->close();

	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}

}


?>