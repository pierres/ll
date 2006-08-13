<?php

require ('modules/IOutput.php');

abstract class GetFile extends Modul implements IOutput{

public function prepare()
	{
	$this->exitIfCached();

	if (!$this->isUser())
		{
		$this->showWarning('Nur für Mitglieder!');
		}

	$this->getParams();
	$this->initDB();
	}

protected function isUser()
	{
	return $this->Io->isRequest('sessionid');
	}

protected function exitIfCached()
	{
	$headers = apache_request_headers();

	if (isset($headers['If-Modified-Since']))
		{
		header('HTTP/1.1 304 Not Modified');
		exit;
		}
	}

protected function getParams()
	{
	}

protected function initDB()
	{
	self::__set('DB', new DB(
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database')
		));
	}

public function showWarning($text)
	{
	die($text);
	}

protected function sendFile($type, $name, $size, $content)
	{
	header('Content-Type: '.$type.'; name='.urlencode($name));
	header('Content-length: '.$size);
	header('Last-Modified: '.date('r'));
	echo $content;
	exit();
	}

}


?>