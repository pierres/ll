<?php

class Stack{


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