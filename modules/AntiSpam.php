<?php

class AntiSpam extends Modul{

/** TODO: Test sschreiben ! */


public function isSpam($text)
	{
	try
		{
		$blacklist = $this->DB->getColumnSet
			('
			SELECT
				domain
			FROM
				domain_blacklist
			');
		}
	catch (DBNoDataException $e)
		{
		$blacklist = array();
		}

	return $this->isInBlacklist($this->getDomains($text), $blacklist);
	}
/** TODO: hier kann man prepared Statements besser verwenden -> Änderung in DB nötig */
public function addSpam($text)
	{
	try
		{
		$blacklist = $this->DB->getColumnSet
			('
			SELECT
				domain
			FROM
				domain_blacklist
			');
		}
	catch (DBNoDataException $e)
		{
		$blacklist = array();
		}

	foreach (array_diff($this->getDomains($text), $blacklist) as $domain)
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				domain_blacklist
			SET
				domain = ?
			');
		$stm->bindString($domain);
		$stm->execute();
		}
	}


private function getDomains($text)
	{
	$protocoll 	= '(?:https?|ftp):\/\/';
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*[a-z0-9])?';
	$tld 		= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;
// 	$address	= '(?:'.$domain.'|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})';

	preg_match_all('/(?:'.$protocoll.'(?:www\.)?|www\.)('.$domain.')/', $text, $domains);

	return array_unique($domains[1]);
	}

private function isInBlacklist($domains, $blacklist)
	{
	foreach ($domains as $domain)
		{
		if (in_array($domain, $blacklist))
			{
			return true;
			}
		}

	return false;
	}


}


?>