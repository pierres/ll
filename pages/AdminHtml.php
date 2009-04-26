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

class AdminHtml extends AdminForm {


protected function setForm()
	{
	$this->setTitle('HTML-Vorlage');

	$this->add(new SubmitButtonElement('Speichern'));

	$inputTextarea = new TextareaInputElement('html', $this->Board->getHtml(), 'HTML');
	$inputTextarea->setMinLength(100);
	$inputTextarea->setMaxLength(50000);
	$this->add($inputTextarea);
	}

protected function checkForm()
	{
	if (!preg_match('<!-- body -->', $this->Input->Post->getString('html')))
		{
		$this->showWarning('Der body-Tag fehlt!');
		}

	if (!preg_match('<!-- title -->', $this->Input->Post->getString('html')))
		{
		$this->showWarning('Der title-Tag fehlt!');
		}

	if (!preg_match('<!-- menu -->', $this->Input->Post->getString('html')))
		{
		$this->showWarning('Der menu-Tag fehlt!');
		}

	if (!preg_match('<!-- user -->', $this->Input->Post->getString('html')))
		{
		$this->showWarning('Der user-Tag fehlt!');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			html = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->Input->Post->getString('html'));
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$this->Output->redirect('AdminHtml');
	}

}


?>