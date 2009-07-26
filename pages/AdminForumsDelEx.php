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

class AdminForumsDelEx extends AdminForm {

private $cat = 0;
private $forum = 0;


public function setForm()
	{
	try
		{
		$this->forum = $this->Input->Get->getInt('forum');
		}
	catch (RequestException $e)
		{
		$this->Output->redirect('AdminCats');
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
		$this->Output->redirect('AdminCats');
		}

	$this->setTitle('Externes Forum löschen');

	$this->setParam('forum', $this->forum);

	$this->add(new SubmitButtonElement('Externes Forum löschen'));
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

	$this->Output->redirect('AdminForums', array('cat' => $this->cat));
	}

}


?>