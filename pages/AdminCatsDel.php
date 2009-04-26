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

class AdminCatsDel extends AdminForm {

private $cat = 0;

protected function setForm()
	{
	try
		{
		$this->cat = $this->Input->Get->getInt('cat');
		}
	catch(RequestException $e)
		{
		$this->showWarning('Keine Kategorien angegeben.');
		}

	$this->setTitle('Kategorien löschen');
	$this->setParam('cat', $this->cat);

// 	$this->addOutput('Hierdurch werden allen enthaltenen Foren und Beiträge unwiederruflich gelöscht!');

	$this->add(new SubmitButtonElement('Kategorie löschen'));
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
				boardid = ?
				AND id = ?'
			);
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->cat);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Kategorie nicht gefunden.');
		}
	}

protected function sendForm()
	{
	AdminFunctions::delCat($this->cat);
	$this->Output->redirect('AdminCats');
	}

}


?>