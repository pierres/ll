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

class AdminGlobalForumsMove extends AdminForm {

private $cat = 0;
private $forum = 0;

protected function setForm()
	{
	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff');
		}

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
				cats
			WHERE
				forum_cat.catid = cats.id
				AND forum_cat.forumid = ?'
			);
		$stm->bindInteger($this->forum);
		$this->cat = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Output->redirect('AdminCats');
		}

	$this->setTitle('Forum verschieben');

	$this->add(new SubmitButtonElement('Verschieben'));

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				(SELECT name FROM boards WHERE id = cats.boardid) AS board
			FROM
				cats
			WHERE
				id != ?
				AND cats.boardid <> ?
			ORDER BY board ASC
			');
		$stm->bindInteger($this->cat);
		$stm->bindInteger($this->Board->getId());

		$inputRadio = new RadioInputElement('newcat', 'Ziel');
		foreach ($stm->getRowSet() as $cat)
			{
			$inputRadio->addOption('<strong>'.$cat['board'].'</strong> '.$cat['name'], $cat['id']);
			}
		$this->add($inputRadio);

		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->setParam('forum', $this->forum);
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
				id = ?'
			);
		$stm->bindInteger($this->Input->Post->getInt('newcat'));
		$stm->getColumn();
		$stm->close();
		}
	catch(DBNoDataException $e)
		{
		$stm->close();
		$this->Output->redirect('AdminCats');
		}
	catch(RequestException $e)
		{
		$stm->close();
		$this->Output->redirect('AdminCats');
		}
	}

protected function sendForm()
	{
	/** FIXME: We should remove all links to this forum existing in the target category */
	$stm = $this->DB->prepare
		('
		UPDATE
			forum_cat
		SET
			catid = ?
		WHERE
			catid = ?
			AND forumid = ?'
		);
	$stm->bindInteger($this->Input->Post->getInt('newcat'));
	$stm->bindInteger($this->cat);
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			forums
		SET
			boardid = (SELECT boardid FROM cats WHERE id = ?)
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Input->Post->getInt('newcat'));
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('AdminForums', array('cat' => $this->Input->Post->getInt('newcat')));
	}


}


?>