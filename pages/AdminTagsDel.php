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
class AdminTagsDel extends AdminForm{

private $tag = 0;

protected function setForm()
	{
	try
		{
		$this->tag = $this->Io->getInt('tag');
		}
	catch(IoRequestException $e)
		{
		$this->redirect();
		}

	$this->setValue('title', 'Tag löschen');
	$this->addHidden('tag', $this->tag);
	$this->requires('tag');

	$this->addOutput('Hierdurch wird auch der entsprechende Tag von allen Themen entfernt!');

	$this->addSubmit('Tag löschen');
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
				tags
			WHERE
				boardid = ?
				AND id = ?'
			);
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->tag);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$this->redirect();
		$stm->close();
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			tags
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->tag);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			tag = 0
		WHERE
			tag = ?'
		);
	$stm->bindInteger($this->tag);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('AdminTags');
	}

}


?>