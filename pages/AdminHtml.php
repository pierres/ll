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
	foreach (array('body', 'user-menu', 'user-welcome', 'main-menu', 'title', 'name') as $tag)
		{
		if (!preg_match('<!-- '.$tag.' -->', $this->Input->Post->getString('html')))
			{
			$this->showWarning('Der &quot;&lt;!-- '.$tag.' --&gt;&quot;-Tag fehlt');
			}
		}
	}

protected function sendForm()
	{
	$html = str_replace("\r", '', $this->Input->Post->getString('html'));

	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			html = ?
		WHERE
			id = ?'
		);
	$stm->bindString($html);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$this->Output->redirect('AdminHtml');
	}

}


?>