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
class AdminCss extends AdminForm{


protected function setForm()
	{
	$this->setValue('title', 'CSS-Vorlage');

	$this->addSubmit('Speichern');

	$stm = $this->DB->prepare
		('
		SELECT
			css
		FROM
			boards
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->Board->getId());
	$css = $stm->getColumn();
	$stm->close();

	$this->addTextArea('css', 'CSS', $css);
	$this->requires('css');
	$this->setLength('css', 100, 50000);
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			css = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->Input->Request->getString('css'));
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('AdminCss');
	}

}


?>