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
class MarkupTest extends Form{

protected $text 	= '';
protected $smilies 	= true;
protected $title 	= 'Markup-Test';


protected function setForm()
	{
	$this->setValue('title', $this->title);

	$this->addSubmit('Abschicken');

	if (!$this->Input->Request->isEmpty('text'))
		{
		$this->text = $this->Input->Request->getString('text');
		$this->Markup->enableSmilies($this->Input->Request->isValid('smilies'));

		$html = $this->Markup->toHtml($this->text);

		$this->addElement('previewwindow',
		'<div class="preview">'.$html.'</div>');

		$this->addElement('html',
		'<pre class="preview">'.htmlspecialchars($html).'</pre>');

		$this->addElement('summary',
		'<pre class="preview">'.getTextFromHtml($html).'</pre>');

		$this->addElement('unmarkup',
		'<pre class="preview">'.htmlspecialchars($this->text = $this->UnMarkup->fromHtml($html)).'</pre>');
		}

	$this->addTextarea('text', 'Deine Nachricht', $this->text);
	$this->requires('text');
	$this->setLength('text', 3, 65536);

	$this->addCheckbox('smilies', 'grafische Smilies', $this->smilies);
	}

protected function checkForm()
	{
	$this->smilies = $this->Input->Request->isValid('smilies');
 	$this->text = $this->Input->Request->getString('text');
	}

protected function sendForm()
	{
	$this->showForm();
	}

}


?>