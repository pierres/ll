<?php


class GetFile extends Page{

private $data = array();

public function prepare()
	{
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
				size,
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
	header('Pragma: public');
	header('Content-Type: '.$this->data['type'].'; name='.$this->data['name']);
	header('Content-Disposition: inline; filename="'.$this->data['name'].'"');
	header('Content-length: '.$this->data['size']);

	echo $this->data['content'];
	exit();
	}

}


?>