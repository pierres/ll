<?php


class AdminHtml extends AdminForm{


protected function setForm()
	{
	$this->setValue('title', 'HTML-Vorlage');

	$this->addSubmit('Speichern');

	$this->addTextArea('html', 'HTML', $this->Board->getHtml());
	$this->requires('html');
	$this->setLength('html', 100, 50000);
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
	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			html = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->Io->getString('html'));
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminHtml');
	}

}


?>