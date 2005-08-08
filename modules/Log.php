<?php

/**
* Verwaltung zur Anzeige, ob ein Beitag bereits gelesen wurde oder nicht
*
* @author Pierre Schmitz
*/
class Log extends Modul{

private $timeout;
private $log;


public function __construct()
	{
	if (!$this->User->isOnline())
		{
		return false;
		}

	$this->timeout = 86400 * Settings::LOG_TIMEOUT; //days
/*
	Das ist keine schöne Lösung;
	mir fälllt z.Z. aber keine bessere ein.

	Das könnte ggf sehr viel Speicher brauchen!
*/
	try
		{
		$temp = $this->Sql->fetch
			('
			SELECT
				threadid,
				dat
			FROM
				threads_log
			WHERE
				userid = '.$this->User->getId()
			);
		}
	catch(SqlNoDataException $e)
		{
		$temp = array();
		}

	foreach($temp as $temp2)
		{
		$this->log[$temp2['threadid']] = $temp2['dat'];
		}
	}

public function insert($threadid, $threadtime)
	{
	if (!$this->User->isOnline())
		{
		return false;
		}

	$time = time();
	if (($time  - $threadtime) >= $this->timeout)
		{
		return false;
		}

	if (!$this->isNew($threadid, $threadtime))
		{
		return false;
		}

	if (empty($this->log[$threadid]))
		{
		$this->Sql->query
			('
			INSERT INTO
				threads_log
			SET
				dat = '.$threadtime.',
				threadid = '.$threadid.',
				userid = '.$this->User->getId()
			);
		}
	else
		{
		$this->Sql->query
			('
			UPDATE
				threads_log
			SET
				dat = '.$threadtime.'
			WHERE
				threadid = '.$threadid.'
				AND userid = '.$this->User->getId()
			);
		}

	/** FIXME: kann man beim Neuerstellen/Löschen eines Beitrags starten.*/
	$this->collectGarbage();
	}

public function isNew($threadid, $threadtime)
	{
	if (!$this->User->isOnline())
		{
		return false;
		}

	if ((time() - $threadtime) >= $this->timeout)
		{
		return false;
		}

	if (empty($this->log[$threadid]) || ($threadtime > $this->log[$threadid]))
		{
		return true;
		}
	else
		{
		return false;
		}
	}

public function getTime($threadid)
	{
	if (!$this->User->isOnline() || empty($this->log[$threadid]))
		{
		return 0;
		}

	return $this->log[$threadid];
	}

public function delete($threadid)
	{
	$this->Sql->query
		('
		DELETE FROM
			threads_log
		WHERE
			threadid = '.$threadid
		);
	}

private function collectGarbage()
	{
	$deltime = time() - $this->timeout;

	$this->Sql->query
		('
		DELETE FROM
			threads_log
		WHERE
			dat <= '.$deltime
		);
    }

}
?>