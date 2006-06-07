<?php

class AntiSpam extends Modul{

private $text = '';
private $listedDomains = array();
private $nonListedDomains = array();


public function __construct($text)
	{
	$this->text = $text;

	$allDomains = $this->getDomains($this->text);

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

	$this->listedDomains = $this->getDomainsInBlacklist($allDomains, $blacklist);
	$this->nonListedDomains = array_diff($allDomains, $blacklist);
	}

public function isSpam()
	{
	if (empty($this->listedDomains))
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

	foreach ($this->listedDomains as $domain)
		{
		$stm->bindString($domain);
		$stm->execute();
		}
	$stm->close();

	return true;
	}

public function getNonListedDomains()
	{
	return $this->nonListedDomains;
	}

public function getListedDomains()
	{
	return $this->listedDomains;
	}


private function getDomains($text)
	{
	$protocoll 	= '(?:https?|ftp):\/\/';
	$name 		= '[a-z0-9](?:[a-z0-9_\-\.]*[a-z0-9])?';
	$tld 		= '[a-z]{2,5}';
	$domain		=  $name.'\.'.$tld;
// 	$address	= '(?:'.$domain.'|[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3})';

	preg_match_all('/(?:'.$protocoll.'(?:www\.)?|www\.|'.$name.'@)('.$domain.')/', $text, $domains);

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