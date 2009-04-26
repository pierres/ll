<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

abstract class GetFile extends Modul implements IOutput {

public function prepare()
	{
	$this->exitIfCached();
	$this->initDB();
	$this->getParams();
	}

protected function exitIfCached()
	{
	if ($this->Input->Server->isString('HTTP_IF_MODIFIED_SINCE')
	&& strtotime($this->Input->Server->getString('HTTP_IF_MODIFIED_SINCE')) <= $this->Input->getTime() - $this->Settings->getValue('file_refresh'))
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
	$this->DB->connect(
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database')
		);
	}

public function showWarning($text)
	{
	die($text);
	}

protected function sendFile($type, $name, $content, $disposition = 'attachment')
	{
	header('HTTP/1.1 200 OK');
	header('Content-Type: '.$type);
	header('Content-Length: '.strlen($content));
	header('Content-Disposition: '.$disposition.'; filename="'.urlencode($name).'"');
	header('Last-Modified: '.date('r'));
	echo $content;
	exit;
	}

protected function sendInlineFile($type, $name, $content)
	{
	$this->sendFile($type, $name, $content, 'inline');
	}

}


?>