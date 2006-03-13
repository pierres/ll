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

	$this->timeout = 86400 * $this->Settings->getValue('log_timeout'); //days
/*
	Das ist keine schöne Lösung;
	mir fälllt z.Z. aber keine bessere ein.

	Das könnte ggf sehr viel Speicher brauchen!
*/
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threadid,
				dat
			FROM
				threads_log
			WHERE
				userid = ?'
			);
		$stm->bindInteger($this->User->getId());

		foreach($stm->getRowSet() as $thread)
			{
			$this->log[$thread['threadid']] = $thread['dat'];
			}
		}
	catch (DBNoDataException $e)
		{
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
		$stm = $this->DB->prepare
			('
			INSERT INTO
				threads_log
			SET
				dat = ?,
				threadid = ?,
				userid = ?'
			);

		}
	else
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				threads_log
			SET
				dat = ?
			WHERE
				threadid = ?
				AND userid = ?'
			);
		}

	$stm->bindInteger($threadtime);
	$stm->bindInteger($threadid);
	$stm->bindInteger($this->User->getId());
	$stm->execute();

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
	if (empty($this->log[$threadid]))
		{
		return (time() - $this->timeout);
		}

	return $this->log[$threadid];
	}

public function delete($threadid)
	{
	try
		{
		$stm = $this->DB->prepare
			('
			DELETE FROM
				threads_log
			WHERE
				threadid = ?'
			);
		$stm->bindInteger($threadid);
		$stm->execute();
		}
	catch (DBNoDataException $e)
		{
		}
	}

private function collectGarbage()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			DELETE FROM
				threads_log
			WHERE
				dat <= ?'
			);
		$stm->bindInteger(time() - $this->timeout);
		$stm->execute();
		}
	catch (DBNoDataException $e)
		{
		}
	}

}
?>