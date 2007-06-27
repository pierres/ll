<?php


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
	$stm->bindString($this->Io->getString('css'));
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminCss');
	}

}


?>