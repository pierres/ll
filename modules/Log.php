<?php

/**
* Verwaltung zur Anzeige, ob ein Beitag bereits gelesen wurde oder nicht
*
* @author Pierre Schmitz
*/
class Log extends Modul{

private $timeout 	= 0;
private $log 		= array();
private $now		= 0;


public function __construct()
	{
	$this->now = time();
	$this->timeout = $this->Settings->getValue('log_timeout');

	if (!$this->User->isOnline())
		{
		return false;
		}

/** TODO
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
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

public function insert($threadid, $threadtime)
	{
	if (!$this->User->isOnline())
		{
		return false;
		}

	if (($this->now  - $threadtime) >= $this->timeout)
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
	$stm->close();
	}

public function isNew($threadid, $threadtime)
	{
	if (!$this->User->isOnline())
		{
		return false;
		}

	if (($this->now - $threadtime) >= $this->timeout)
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
		return ($this->now - $this->timeout);
		}

	return $this->log[$threadid];
	}

// private function delete($threadid)
// 	{
// 	$stm = $this->DB->prepare
// 		('
// 		DELETE FROM
// 			threads_log
// 		WHERE
// 			threadid = ?'
// 		);
// 	$stm->bindInteger($threadid);
// 	$stm->execute();
// 	$stm->close();
// 	}

public function collectGarbage()
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			threads_log
		WHERE
			dat <= ?'
		);
	$stm->bindInteger($this->now - $this->timeout);
	$stm->execute();
	$stm->close();
	}

}
?>