<?php


class GetFile extends Page{

private $data = array();

public function prepare()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
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
		$this->data = $this->Sql->fetchRow
			('
			SELECT
				name,
				type,
				content
			FROM
				files
			WHERE
				id = '.$file
			);
		}
	catch (SqlNoDataException $e)
		{
		$this->showWarning('Datei nicht gefunden');
		}
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
	header('Content-length: '.strlen($this->data['content']));

	echo $this->data['content'];
	exit();
	}

}


?>