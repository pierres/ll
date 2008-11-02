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

class Request {

private $request = array();

public function __construct(&$request)
	{
	$this->request = &$request;
	}

public function isValid($name)
	{
	return isset($this->request[$name]) && is_unicode($this->request[$name]);
	}

public function isEmpty($name)
	{
	return !$this->isValid($name) || (strlen($this->request[$name]) == 0);
	}

public function isEmptyString($name)
	{
	if(!$this->isEmpty($name))
		{
		$request = trim($this->request[$name]);
		return empty($request);
		}
	else
		{
		return true;
		}
	}

public function getString($name)
	{
	if ($this->isValid($name))
		{
		$this->request[$name] = trim($this->request[$name]);
		return $this->request[$name];
		}
	else
		{
		throw new RequestException($name);
		}
	}

public function getInt($name)
	{
	return intval($this->getString($name));
	}

public function getHex($name)
	{
	return hexVal($this->getString($name));
	}

public function getHtml($name)
	{
	return htmlspecialchars($this->getString($name), ENT_COMPAT);
	}

private function checkArray(&$value, $key)
	{
	if (!is_unicode($value))
		{
		throw new RequestException($key);
		}

	$value = trim($value);
	}

public function getArray($name)
	{
	if(isset($this->request[$name]) && is_array($this->request[$name]))
		{
		array_walk_recursive($this->request[$name], array($this, 'checkArray'));

		return $this->request[$name];
		}
	else
		{
		throw new RequestException($name);
		}
	}

public function getLength($name)
	{
	return $this->isEmpty($name) ? 0 : strlen($this->getString($name));
	}
}

class RequestException extends RuntimeException {

function __construct($message)
	{
	parent::__construct('Der Parameter "'.$message.'" wurde nicht übergeben.', 0);
	}

}

?>