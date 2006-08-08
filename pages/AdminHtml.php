<?php


class AdminHtml extends AdminForm{


protected function setForm()
	{
	$this->setValue('title', 'HTML-Vorlage');

	$this->addSubmit('Speichern');

	$html = file_get_contents('html/'.$this->Board->getId().'.html');

	$this->addTextArea('html', 'HTML', $html);
	$this->requires('html');
	$this->setLength('html', 100, 100000);
	}

protected function checkForm()
	{
	if (!preg_match('<!-- body -->', $this->Io->getString('html')))
		{
		$this->showWarning('Der body-Tag fehlt!');
		}

	if (!preg_match('<!-- title -->', $this->Io->getString('html')))
		{
		$this->showWarning('Der body-Tag fehlt!');
		}

	if (!preg_match('<!-- menu -->', $this->Io->getString('html')))
		{
		$this->showWarning('Der menu-Tag fehlt!');
		}

	if (!preg_match('<!-- user -->', $this->Io->getString('html')))
		{
		$this->showWarning('Der user-Tag fehlt!');
		}
	}

protected function sendForm()
	{
	file_put_contents('html/'.$this->Board->getId().'.html', $this->Io->getString('html'));

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminHtml');
	}

}


?>