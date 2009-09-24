<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class AdminForumsEx extends AdminForm {

private $cat = 0;


protected function setForm()
	{
	try
		{
		$this->cat = $this->Input->Get->getInt('cat');
		}
	catch (RequestException $e)
		{
		$this->Output->redirect('AdminCats');
		}

	$this->setTitle('Externe Foren hinzufügen');
	$this->add(new SubmitButtonElement('Hinzufügen'));

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
				$this->add(new PassiveFormElement('<div style="margin:8px"><strong>&#171; '.$forum['boardname'].' &#187;</strong></div>'));
				}
			$board = $forum['boardid'];

			$this->add(new PassiveFormElement
				('
				<input type="checkbox" name="forums[]" value="'.$forum['id'].'" />'.$forum['name'].'<br />
				'));
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->setParam('cat', $this->cat);
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
		$this->Output->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	try
		{
		$forums = $this->Input->Post->getArray('forums');
		}
	catch (RequestException $e)
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
	$this->Output->redirect('AdminForums', array('cat' => $this->cat));
	}


}

?>