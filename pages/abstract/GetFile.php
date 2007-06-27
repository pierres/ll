<?php


abstract class GetFile extends Modul implements IOutput{

public function prepare()
	{
	$this->exitIfCached();
	$this->initDB();

	if (!$this->User->isOnline())
		{
		$this->showWarning('Nur für Mitglieder!');
		}

	$this->getParams();
	}

protected function exitIfCached()
	{
	if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
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

protected function sendFile($type, $name, $size, $content, $disposition = 'attachment')
	{
	header('HTTP/1.1 200 OK');
	header('Content-Type: '.$type);
	header('Content-Length: '.$size);
	header('Content-Disposition: '.$disposition.'; filename="'.urlencode($name).'"');
	header('Last-Modified: '.date('r'));
	echo $content;
	exit;
	}

protected function sendInlineFile($type, $name, $size, $content)
	{
	$this->sendFile($type, $name, $size, $content, 'inline');
	}

}


?>