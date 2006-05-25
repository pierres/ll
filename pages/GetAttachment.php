<?php

class GetAttachment extends GetFile{

/** TODO: Vorschau für Bilder */

protected $file = 0;

protected function getParams()
	{
	try
		{
		$this->file = $this->Io->getInt('file');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('keine Datei angegeben');
		}
	}

public function show()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				name,
				type,
				content,
				size
			FROM
				attachments
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->file);
		$data = $stm->getRow();
		}
	catch (DBNoDataException $e)
		{
		$this->showWarning('Datei nicht gefunden');
		}

	$this->sendFile($data['type'], $data['name'], $data['size'], $data['content']);
	}

}

?>