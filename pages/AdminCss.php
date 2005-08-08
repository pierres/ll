<?php


class AdminCss extends AdminForm{


protected function setForm()
	{
	$this->setValue('title', 'CSS-Vorlage');

	$this->addSubmit('Speichern');

	$html = file_get_contents(PATH.'html/'.$this->Board->getId().'.css');

	$this->addTextArea('css', 'CSS', $html);
	$this->requires('css');
	$this->setLength('css', 100, 100000);
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	file_put_contents(PATH.'html/'.$this->Board->getId().'.css', $this->Io->getString('css'));

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminCss');
	}

}


?>