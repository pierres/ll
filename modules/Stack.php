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

class Stack {

private $array = array();
private $count = 0;


public function pop()
	{
	if ($this->count > 0)
		{
		$this->count--;
		return array_pop($this->array);
		}
	else
		{
		return null;
		}
	}

public function push($value)
	{
	$this->count++;
	return array_push($this->array, $value);
	}

public function last()
	{
	return ($this->count > 0 ? $this->array[$this->count-1] : null);
	}

public function fetch($id)
	{
	if (isset($this->array[$id]))
		{
		return $this->array[$id];
		}
	else
		{
		return null;
		}
	}

public function lastID()
	{
	return ($this->count > 0 ? $this->count-1 : null);
	}

public function hasNext()
	{
	return ($this->count > 0 ? true : false);
	}

}

?>