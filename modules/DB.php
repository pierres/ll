<?php


class DB extends Modul{

private $link = null;

function __construct($user, $password, $database)
	{
	$this->link = mysqli_connect(null, $user, $password, $database);

	if (!$this->link)
		{
		throw new DBConnectException();
		}
	}

function __destruct()
	{
	@mysqli_close($this->link);
	}

public function getInsertId()
	{
	$id = mysqli_insert_id($this->link);

	if ($id == 0)
		{
		throw new DBException($this->link);
		}

	return $id;
	}

public function prepare($query)
	{
	if (!$stm = mysqli_prepare($this->link, $query))
		{
		throw new DBException($this->link, $query);
		}

	return new DBStatement($stm, $this->link);
	}

public function execute($query)
	{
	$result = mysqli_query($this->link, $query);

	if (!$result)
		{
		throw new DBException($this->link);
		}

	if (mysqli_warning_count($this->link))
		{
		@mysqli_free_result($result);
		throw new DBWarningException($this->link);
		}

	if (mysqli_affected_rows($this->link) <= 0)
		{
		@mysqli_free_result($result);
		throw new DBNoDataException();
		}

	return $result;
	}

private function query($query)
	{
	$result = mysqli_query($this->link, $query, MYSQLI_STORE_RESULT);

	if (!$result)
		{
		throw new DBException($this->link);
		}

	if (mysqli_warning_count($this->link))
		{
		@mysqli_free_result($result);
		throw new DBWarningException($this->link);
		}

	if (mysqli_num_rows($result) == 0)
		{
		@mysqli_free_result($result);
		throw new DBNoDataException();
		}

	return $result;
	}

public function getRowSet($query)
	{
	return new DBResult($this->query($query));
	}

public function getRow($query)
	{
	$result = $this->query($query);
	if ($row = mysqli_fetch_assoc($result))
		{
		mysqli_free_result($result);
		return $row;
		}
	else
		{
		@mysqli_free_result($result);
		throw new DBNoDataException($this->link);
		}
	}

public function getColumn($query)
	{
	$result = $this->query($query);
	if ($row = mysqli_fetch_array($result, MYSQLI_NUM))
		{
		mysqli_free_result($result);
		return $row[0];
		}
	else
		{
		@mysqli_free_result($result);
		throw new DBNoDataException($this->link);
		}
	}

public function getNumRows()
	{
	return mysqli_num_rows($this->link);
	}

}

// ------------------------------------------------------------------------------------------------------

class DBException extends RuntimeException {

function __construct($link)
	{
	parent::__construct(mysqli_error($link), mysqli_errno($link));
	}
}

class DBNoDataException extends DBException{

function __construct()
	{
	RuntimeException::__construct('', 1);
	}
}

class DBStatementException extends DBException {

function __construct($link)
	{
	RuntimeException::__construct(mysqli_stmt_error($link), mysqli_stmt_errno($link));
	}
}

class DBConnectException extends DBException {

function __construct()
	{
	RuntimeException::__construct(mysqli_connect_error(), mysqli_connect_errno());
	}
}

class DBWarningException extends DBException {

function __construct($link)
	{
	$code = 0;
	$error = '';

	if ($result = mysqli_query($link, 'SHOW WARNINGS'))
		{
		$row = mysqli_fetch_row($result);
		$code = $row[1];
		$error = $row[0].' : '.$row[2];
		mysqli_free_result($result);
		}

	RuntimeException::__construct($error, $code);
	}
}
// ------------------------------------------------------------------------------------------------------

interface IDBResult extends Iterator{}

class DBResult implements IDBResult{

private $result		= null;
private $row 		= null;
private $current 	= 0;

public function __construct($result)
	{
	$this->result = $result;
	}

public function current()
	{
	return $this->row;
	}

public function key()
	{
	return $this->current;
	}

public function next()
	{
	}

public function rewind()
	{
	if ($this->current > 0)
		{
		mysqli_free_result($this->result);
		$this->current = 0;
		}
	}

public function valid()
	{
	if ($this->row = mysqli_fetch_assoc($this->result))
		{
		$this->current++;
		return true;
		}
	else
		{
		$this->rewind();
		return false;
		}
	}

}


// ------------------------------------------------------------------------------------------------------


class DBStatement{

private $link 			= null;
private $stm 		= null;
private $bindings 	= array();
private $types 		= '';

public function __construct($stm, $link)
	{
	$this->stm = $stm;
	$this->link = $link;
	}

public function __destruct()
	{
	}

public function close()
	{
	mysqli_stmt_close($this->stm);
	}

public function bindString($value)
	{
	$this->bindings[] = $value;
	$this->types .= 's';
	}

public function bindDouble($value)
	{
	$this->bindings[] = $value;
	$this->types .= 'd';
	}

public function bindInteger($value)
	{
	$this->bindings[] = $value;
	$this->types .= 'i';
	}

public function bindBinary($value)
	{
	$this->bindings[] = $value;
	$this->types .= 'b';
	}

private function bindParams($types, $values)
	{
	$params = array_merge(array($this->stm, $types), $values);
	if (!call_user_func_array('mysqli_stmt_bind_param', $params))
		{
		throw new DBStatementException($this->stm);
		}
	}

private function executeStatement()
	{
	if (!empty($this->types))
		{
		$this->bindParams($this->types, $this->bindings);
		}

	if (!mysqli_stmt_execute($this->stm))
		{
		throw new DBStatementException($this->stm);
		}

	if (mysqli_warning_count($this->link))
		{
		$this->close();
		throw new DBWarningException($this->link);
		}

	if (!mysqli_stmt_store_result($this->stm))
		{
		throw new DBStatementException($this->stm);
		}

	if (mysqli_stmt_num_rows($this->stm) == 0)
		{
		throw new DBNoDataException();
		}
	}

public function execute()
	{
	if (!empty($this->types))
		{
		$this->bindParams($this->types, $this->bindings);
		}

	if (!mysqli_stmt_execute($this->stm))
		{
		throw new DBStatementException($this->stm);
		}

	if (mysqli_warning_count($this->link))
		{
		$this->close();
		throw new DBWarningException($this->link);
		}

	if (mysqli_stmt_affected_rows($this->stm) <= 0)
		{
		throw new DBNoDataException();
		}

	$this->close();
	}

private function bindResult()
	{
	if (!$data = mysqli_stmt_result_metadata($this->stm))
		{
		throw new DBStatementException($this->stm);
		}

	$params[] = &$this->stm;

	while ($field = mysqli_fetch_field($data))
		{
		$params[] = &$row[$field->name];
		}

	call_user_func_array('mysqli_stmt_bind_result', $params);

	return $row;
	}

public function getRowSet()
	{
	$this->executeStatement();
	$row = $this->bindResult();
	return new DBStatementResult($this->stm, $row);
	}

public function getRow()
	{
	$this->executeStatement();
	$row = $this->bindResult();

	$result = mysqli_stmt_fetch($this->stm);

	if ($result == true)
		{
		$this->close();
		return $row;
		}
	elseif($result == null)
		{
		$this->close();
		throw new DBNoDataException();
		}
	else
		{
		throw new DBStatementException($this->stm);
		}
	}

public function getColumnSet()
	{
	$this->executeStatement();
	$column = null;
	mysqli_stmt_bind_result($this->stm, $column);
	return new DBStatementResult($this->stm, $column);
	}

public function getColumn()
	{
	$this->executeStatement();
	$column = null;
	mysqli_stmt_bind_result($this->stm, $column);
	$result = mysqli_stmt_fetch($this->stm);

	if ($result == true)
		{
		$this->close();
		return $column;
		}
	elseif($result == null)
		{
		$this->close();
		throw new DBNoDataException();
		}
	else
		{
		throw new DBStatementException($this->stm);
		}
	}

public function getNumRows()
	{
	return mysqli_stmt_num_rows($this->stm);
	}

}

// ------------------------------------------------------------------------------------------------------

class DBStatementResult implements IDBResult{

private $stm 		= null;
private $row 		= null;
private $current 	= 0;

public function __construct($stm, &$row)
	{
	$this->stm = $stm;
	$this->row = &$row;
	}

public function current()
	{
	return $this->row;
	}

public function key()
	{
	return $this->current;
	}

public function next()
	{
	}

public function rewind()
	{
	if ($this->current > 0)
		{
		mysqli_stmt_close($this->stm);
		$this->current = 0;
		}
	}

public function valid()
	{
	$result = mysqli_stmt_fetch($this->stm);

	if ($result === true)
		{
		$this->current++;
		return true;
		}
	elseif ($result === null)
		{
		$this->rewind();
		return false;
		}
	else
		{
		throw new DBStatementException($this->stm);
		}
	}

}



?>