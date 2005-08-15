<?php


class TestUpload extends Form{

private $file = array();

protected function setForm()
	{
	$this->setValue('title', 'Datei hochladen');

	$this->addSubmit('Hochladen');

	$this->addOutput('<input type="file" name="file" />');
	$this->setEncoding('enctype="multipart/form-data"');
	}

protected function checkForm()
	{
	/**
	pro User: Gesamtzahl, Gesamtgröße und Einzelgröße der Dateien beschränken
	Systemweit: Gesamtzahl und Größe
	Avatare vielleicht doch auf HD sichern -> sonst sehr viele DB-Abfragen
	*/
	try
		{
		$this->file = $this->Io->getFile('file');
		}
	catch (IoException $e)
		{
		$this->showFailure('Problem!');
		}

	if ($this->file['size'] >= 524288)
		{
		$this->showWarning('Datei ist zu groß!');
		}
	}

protected function sendForm()
	{
	$this->Sql->query
		('
		INSERT INTO
			files
		SET
			name = \''.$this->Sql->escapeString($this->file['name']).'\',
			type = \''.$this->Sql->escapeString($this->file['type']).'\',
			size = '.intval($this->file['size']).',
			content = \''.$this->Sql->escapeString(file_get_contents($this->file['tmp_name'])).'\',
			userid = '.$this->User->getId().',
			uploaded = '.time()
		);

	$this->Io->redirect('TestUpload');
	}

}

?>