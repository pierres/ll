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