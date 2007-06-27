<?php

class GetCss extends GetFile{

public function prepare()
	{
	$this->exitIfCached();
	$this->initDB();
	}

public function show()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				css
			FROM
				boards
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Board->GetId());
		$css = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->Io->setStatus(Io::NOT_FOUND);
		$this->showWarning('Datei nicht gefunden');
		}

	$this->Io->setContentType('Content-Type: text/css; charset=UTF-8');
	$this->Io->out($css);
	}

}

?>