<?php

class GetId extends GetFile{

private $key 		= '';

public function prepare()
	{
	$this->exitIfCached();
	$this->getParams();
	}

protected function getParams()
	{
	try
		{
		$this->key = $this->Io->getString('key');
		}
	catch (IoRequestException $e)
		{
		$this->showWarning('keinen Schlüssel angegeben');
		}
	}

public function show()
	{
	$this->sendInlineFile('text/plain', 'id.txt', 40, sha1($this->Settings->getValue('id_key').$this->key));
	}

}

?>