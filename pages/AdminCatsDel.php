<?php


class AdminCatsDel extends AdminForm{

private $cat = 0;

protected function setForm()
	{
	try
		{
		$this->cat = $this->Io->getInt('cat');
		}
	catch(IoRequestException $e)
		{
		$this->redirect();
		}

	$this->setValue('title', 'Kategorien löschen');
	$this->addHidden('cat', $this->cat);
	$this->requires('cat');

	$this->addOutput('Hierdurch werden allen enthaltenen Foren und Beiträge unwiederruflich gelöscht!');

	$this->addSubmit('Kategorie löschen');
	}

protected function checkForm()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				cats
			WHERE
				boardid = ?
				AND id = ?'
			);
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->cat);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$this->redirect();
		$stm->close();
		}
	}

protected function sendForm()
	{
	AdminFunctions::delCat($this->cat);
	$this->redirect();
	}


protected function redirect()
	{
	$this->Io->redirect('AdminCats');
	}

}


?>