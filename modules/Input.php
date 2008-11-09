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

require ('modules/Request.php');
require ('modules/File.php');
require ('modules/RemoteFile.php');
require ('modules/UploadedFile.php');

class Input extends Modul {

public $Get 	= null;
public $Post 	= null;
public $Cookie 	= null;
public $Request	= null;
public $Env 	= null;
public $Server 	= null;

public function __construct()
	{
	$_REQUEST = array_merge($_GET, $_POST);

	$this->Get 	= new Request($_GET);
	$this->Post 	= new Request($_POST);
	$this->Cookie 	= new Request($_COOKIE);
	$this->Request 	= new Request($_REQUEST);
	$this->Env 	= new Request($_ENV);
	$this->Server 	= new Request($_SERVER);
	}

public function getHost()
	{
	return $this->Server->getString('HTTP_HOST');
	}

public function getURL()
	{
	return 'http'.(!$this->Server->isValid('HTTPS') ? '' : 's').'://'
			.$this->getHost()
			.dirname($this->Server->getString('SCRIPT_NAME'));
	}

public function getRemoteFile($url)
	{
	return new RemoteFile($url);
	}

public function getUploadedFile($url)
	{
	return new UploadedFile($url);
	}

}

?>