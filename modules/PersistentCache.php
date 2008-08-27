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

class PersistentCache extends Modul implements ICache{

private $time = 0;

public function __construct()
	{
	$this->time = time();
	}

public function addObject($key, $object, $ttl = 0)
	{
	try
		{
		$stm = $this->DB->prepare
			('
			REPLACE INTO
				cache
			SET
				`key` = ?,
				value = ?,
				expires = ?
			');
		$stm->bindString($key);
		$stm->bindString(serialize($object));
		$stm->bindInteger(($this->time + $ttl));
		$stm->execute();
		}
	catch (DBException $e)
		{
		}
	$stm->close();
	}

public function getObject($key)
	{
	$this->collectGarbage();

	$value = false;

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				value
			FROM
				cache
			WHERE
				`key` = ?
			');
		$stm->bindString($key);

		$value = unserialize($stm->getColumn());
		}
	catch (DBNoDataException $e)
		{
		}
	$stm->close();

	return $value;
	}

private function collectGarbage()
	{
	/* Ignore 49% of requests */
	if (!mt_rand(0, 50))
		{
		try
			{
			$stm = $this->DB->prepare
				('
				DELETE FROM
					cache
				WHERE
					expires < ?
				');
			$stm->bindInteger($this->time);
			$stm->execute();
			}
		catch (DBException $e)
			{
			}
		$stm->close();
		}
	}

}

?>