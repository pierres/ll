<?php


class DelFile extends Page{


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
		return;
		}

	try
		{
		$this->Sql->query
			('
			DELETE FROM
				files
			WHERE
				id = '.$file.'
				AND userid = '.$this->User->getId()
			);
		}
	catch (SqlException $e)
		{
		return;
		}
	}

public function show()
	{
	$this->Io->redirect('MyFiles');
	}


}

?>