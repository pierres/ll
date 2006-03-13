<?php


class GetFile extends Page{

private $data = array();

public function prepare()
	{
	if (!$this->User->isOnline())
		{
		$this->showWarning('Nur für Mitglieder!');
		}

	try
		{
		$file = $this->Io->getInt('file');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('keine Datei angegeben');
		}

	try
		{
		/**
		 FIXME: entsprechende BLOB-Befehle von mysqli verwenden
		 TODO: evtl. im Dateisystem zwischenspeichern
		*/
		$stm = $this->DB->prepare
			('
			SELECT
				name,
				type,
				content,
				size
			FROM
				files
			WHERE
				id = ?'
			);
		$stm->bindInteger($file);
		$this->data = $stm->getRow();
		}
	catch (DBNoDataException $e)
		{
		$this->showWarning('Datei nicht gefunden');
		}
	}

public function showWarning($text)
	{
	$this->setValue('title', 'Warnung');
	$this->setValue('body', '<div class="warning">'.$text.'</div>');
	parent::show();
	}

public function show()
	{
	If (strpos($this->Io->getEnv('HTTP_ACCEPT_ENCODING'), 'gzip') !== false)
		{
		header('Content-Encoding: gzip');
		}
	else
		{
		$this->data['content'] = gzdecode($this->data['content']);
		}

	header('Content-Type: '.$this->data['type'].'; name='.$this->data['name']);
	header('Content-Disposition: inline; filename="'.$this->data['name'].'"');
	header('Content-length: '.$this->data['size']);

	echo $this->data['content'];
	exit();
	}

}


?>