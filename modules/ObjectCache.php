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
interface IObjectCache {

public function addObject($key, $object, $ttl = 0);

public function getObject($key);

}

class ObjectCache implements IObjectCache{

private $cache = null;

public function __construct()
	{
	if (function_exists('apc_store'))
		{
		$this->cache = new APCObjectCache();
		}
	elseif (function_exists('xcache_set'))
		{
		$this->cache = new XCacheObjectCache();
		}
	else
		{
		$this->cache = new NOOPObjectCache();
		}
	}

public function addObject($key, $object, $ttl = 0)
	{
	return $this->cache->addObject($key, $object, $ttl);
	}

public function getObject($key)
	{
	return $this->cache->getObject($key);
	}

}

class NOOPObjectCache implements IObjectCache{

public function addObject($key, $object, $ttl = 0)
	{
	return false;
	}

public function getObject($key)
	{
	return false;
	}
}

class APCObjectCache implements IObjectCache{

public function addObject($key, $object, $ttl = 0)
	{
	return apc_store($key, $object, $ttl);
	}

public function getObject($key)
	{
	return apc_fetch($key);
	}
}

class XCacheObjectCache implements IObjectCache{

public function addObject($key, $object, $ttl = 0)
	{
	return xcache_set($key, $object, $ttl);
	}

public function getObject($key)
	{
	return xcache_get($key);
	}
}

?>