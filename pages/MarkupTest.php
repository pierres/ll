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

class MarkupTest extends Form {

protected $text 	= '';
protected $title 	= 'Markup-Test';


protected function setForm()
	{
	$this->setTitle($this->title);

	$this->add(new SubmitButtonElement('Abschicken'));

	if (!$this->Input->Post->isEmptyString('text'))
		{
		$this->text = $this->Input->Post->getString('text');
		$html = $this->Markup->toHtml($this->text);

		$this->add(new LabeledElement('previewwindow',
		'<div>'.$html.'</div>'));

		$this->add(new LabeledElement('html',
		'<pre>'.htmlspecialchars($html).'</pre>'));

		$this->add(new LabeledElement('summary',
		'<pre>'.getTextFromHtml($html).'</pre>'));

		$this->add(new LabeledElement('unmarkup',
		'<pre>'.htmlspecialchars($this->text = $this->UnMarkup->fromHtml($html)).'</pre>'));
		}

	$this->add(new TextareaInputElement('text', $this->text, 'Deine Nachricht'));
	}

protected function checkForm()
	{
 	$this->text = $this->Input->Post->getString('text');
	}

protected function sendForm()
	{
	$this->showForm();
	}

}


?>