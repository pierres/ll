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
				<input type="checkbox" name="forums[]" value="'.$forum['id'].'" />'.$forum['name'].'<br />
				');
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Io->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	try
		{
		$forums = $this->Io->getArray('forums');
		}
	catch (IoRequestException $e)
		{
		$this->redirect();
		}

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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->redirect();
		}

	$stm = $this->DB->prepare
		('
		INSERT INTO
			forum_cat
		SET
			forumid = ?,
			catid = ?,
			position = ?'
		);

	foreach($forums as $forum)
		{
		$stm->bindInteger($forum);
		$stm->bindInteger($this->cat);
		$stm->bindInteger($position);
		$stm->execute();
		}
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForums', 'cat='.$this->cat);
	}


}

?>