<?php


  /** TODO: Commit und Rollback etc.
  *   -> geht nur mit INNODB; das kann aber kein fulltext-index :-(
  *
  */

class Sql extends Modul{

private $link = false;
public $connects = 0;
public $queries = 0;


function __construct()
	{
	$this->connects++;

	$this->link = mysqli_connect('localhost', Settings::SQL_USER, Settings::SQL_PASSWORD, Settings::SQL_DATABASE);

	if (mysqli_connect_errno())
		{
		throw new SqlException($this->link);
		}

	return true;
	}

function __destruct()
	{
	@mysqli_close($this->link);
	}

/** FIXME: hiermit bin ich noch nicht glücklich */
public function formatString($string)
	{
	return htmlspecialchars($this->escapeString($string));
	}

public function escapeString($string)
	{
	return mysqli_real_escape_string($this->link, $string);
	}

public function query($query)
	{
	$this->queries++;

	if (!$result = mysqli_query($this->link, $query))
		{
		throw new SqlException($this->link, $query);
		}

	if (mysqli_warning_count($this->link))
		{
		throw new SqlWarningException($this->link, $query);
		}

	return $result;
	}

public function insertId()
	{
	$this->queries++;

	if (!$result = mysqli_insert_id($this->link))
		{
		throw new SqlException($this->link);
		}

	return $result;
	}

public function numRows($query)
	{
	try
		{
		$rows = $this->fetchValue('SELECT COUNT(*) FROM '.$query);
		}
	catch (SqlNoDataException $e)
		{
		$rows = 0;
		}

	return $rows;
	}

public function fetch($query)
	{
	$this->queries++;

	if (!$result = mysqli_query($this->link, $query))
		{
		throw new SqlException($this->link, $query);
		}

	if (mysqli_num_rows($result) == 0)
		{
		throw new SqlNoDataException($this->link, $query);
		}

	$array = array();

	while($ar = mysqli_fetch_assoc($result))
		{
		$array[] = $ar;
		}

	mysqli_free_result($result);

	return $array;
	}

public function fetchRow($query)
	{
	$result = $this->fetch($query.' LIMIT 1');

	return $result[0];
	}

public function fetchCol($query)
	{
	$this->queries++;

	if (!$result = mysqli_query($this->link, $query))
		{
		throw new SqlException($this->link, $query);
		}

	if (mysqli_num_rows($result) == 0)
		{
		throw new SqlNoDataException($this->link, $query);
		}

	$array = array();

	while($ar = mysqli_fetch_row($result))
		{
		$array[] = $ar[0];
		}

	mysqli_free_result($result);

	return $array;
	}

public function fetchValue($query)
	{
	$result = $this->fetchCol($query.' LIMIT 1');

	return $result[0];
	}

}

class SqlException extends RuntimeException {

protected $query;

function __construct($link, $query = '')
	{
	parent::__construct($query."\n\n". mysqli_error($link), mysqli_errno($link));
	}
}

class SqlWarningException extends SqlException {

function __construct($link, $query)
	{
	parent::__construct($link, $query);

	if ($result = mysqli_query($link, 'SHOW WARNINGS'))
		{
		$row = mysqli_fetch_row($result);
		echo 'Datenbankfehler:<br /><br />'.sprintf("%s (%d): %s\n", $row[0], $row[1], $row[2]);
		mysqli_free_result($result);
		}
	}
}

class SqlNoDataException extends SqlException {}

?>