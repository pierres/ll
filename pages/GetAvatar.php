<?php

class GetAvatar extends GetFile{

private $user = 0;

protected function getParams()
	{
	try
		{
		$this->user = $this->Io->getInt('user');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('kein Benutzer angegeben');
		}
	}

public function show()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				type,
				name,
				content,
				size
			FROM
				avatars
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->user);
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