<?php

class MarkupTest extends Form{

protected $text 	= '';
protected $smilies 	= true;
protected $title 	= 'Markup-Test';


protected function setForm()
	{
	$this->setValue('title', $this->title);

	$this->addSubmit('Abschicken');

	if (!$this->Io->isEmpty('text'))
		{
		$this->text = $this->Io->getString('text');
		$this->Markup->enableSmilies($this->Io->isRequest('smilies'));

		$html = $this->Markup->toHtml($this->text);

		$this->addElement('previewwindow',
		'<div class="preview">'.$html.'</div>');

		$this->addElement('html',
		'<pre class="preview">'.htmlspecialchars($html).'</pre>');

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
	$this->smilies = $this->Io->isRequest('smilies');
 	$this->text = $this->Io->getString('text');
	}

protected function sendForm()
	{
	$this->showForm();
	}

}


?>