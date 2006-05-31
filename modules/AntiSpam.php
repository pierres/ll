<?php

class AntiSpam extends Modul{


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

	$evilDomains = $this->getDomainsInBlacklist($this->getDomains($text), $blacklist);

	if (empty($evilDomains))
		{
		return false;
		}

	$stm = $this->DB->prepare
		('
		UPDATE
			domain_blacklist
		SET
			counter = counter + 1,
			lastmatch = UNIX_TIMESTAMP()
		WHERE
			domain = ?
		');

	foreach ($evilDomains as $evilDomain)
		{
		$stm->bindString($evilDomain);
		$stm->execute();
		}
	$stm->close();

	return true;
	}

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

	$stm = $this->DB->prepare
		('
		INSERT INTO
			domain_blacklist
		SET
			domain = ?,
			inserted = UNIX_TIMESTAMP(),
			lastmatch = UNIX_TIMESTAMP()
		');
	foreach (array_diff($this->getDomains($text), $blacklist) as $domain)
		{
		$stm->bindString($domain);
		$stm->execute();
		}
	$stm->close();
	}


private function getDomains($text)
	{
	$protocoll 	= '(?:https?|ftp):\/\/';
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*[a-z0-9])?';
	$tld 			= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;
// 	$address		= '(?:'.$domain.'|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})';

	preg_match_all('/(?:'.$protocoll.'(?:www\.)?|www\.)('.$domain.')/', $text, $domains);

	return array_unique($domains[1]);
	}

private function getDomainsInBlacklist($domains, $blacklist)
	{
	$matches =array();

	foreach ($domains as $domain)
		{
		if (in_array($domain, $blacklist))
			{
			$matches[] = $domain;
			}
		}

	return $matches;
	}


}


?>