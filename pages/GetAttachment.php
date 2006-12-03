<?php

class GetAttachment extends GetFile{


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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Datei nicht gefunden');
		}

	if (strpos($data['type'], 'image/') === 0)
		{
 		$this->sendInlineFile($data['type'], $data['name'], $data['size'], $data['content']);
		}
	else
		{
		$this->sendFile($data['type'], $data['name'], $data['size'], $data['content']);
		}
	}

}

?>