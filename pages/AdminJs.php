<?php


class AdminJs extends AdminForm{


protected function setForm()
	{
	$this->setValue('title', 'JS-Vorlage');

	$this->addSubmit('Speichern');

	$html = file_get_contents(PATH.'html/'.$this->Board->getId().'.js');

	$this->addTextArea('js', 'JS', $html);
	$this->requires('js');
	$this->setLength('js', 10, 100000);
	}

protected function checkForm()
	{
	}

protected function sendForm()
	{
	file_put_contents(PATH.'html/'.$this->Board->getId().'.js', $this->Io->getString('js'));

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminJs');
	}

}


?>