#!/usr/bin/php
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

if (php_sapi_name() != 'cli')
	{
	die ('run from cli only!');
	}

define('IN_LL', null);

require ('modules/Modul.php');
require ('modules/Settings.php');
require ('modules/Exceptions.php');
require ('modules/Functions.php');
require ('modules/Input.php');
require ('modules/Output.php');
require ('modules/L10n.php');

Modul::set('Settings', new Settings());
$Input = Modul::set('Input', new Input());
Modul::set('L10n', new L10n());
$Output = Modul::set('Output', new Output());

function __autoload($class)
	{
	Modul::loadModul($class);
	}

class Update extends Modul {


public function run()
	{
	set_time_limit(0);

	$this->DB->connect(
		$this->Settings->getValue('sql_host'),
		$this->Settings->getValue('sql_user'),
		$this->Settings->getValue('sql_password'),
		$this->Settings->getValue('sql_database'));
		
	$_SERVER['HTTP_HOST'] = $this->Board->getHost();
	$tables = $this->DB->getColumnSet('SHOW TABLES');
	$this->DB->execute('LOCK TABLES `'.implode('` WRITE, `', $tables).'` WRITE');

	$this->updateDB();
	$this->updateMarkup();

	$this->DB->execute('UNLOCK TABLES');
	}

private function updateDB()
	{
	$this->DB->execute('DROP TABLE `cache`');
	$this->DB->execute
		('CREATE TABLE `search` (
			`id` int(10) unsigned NOT NULL AUTO_INCREMENT,
			`query` varchar(255) NOT NULL,
			`expires` int(10) unsigned NOT NULL,
		PRIMARY KEY (`id`),
		UNIQUE KEY `query` (`query`)
		) ENGINE=MyISAM  DEFAULT CHARSET=utf8');
	$this->DB->execute
		('CREATE TABLE `search_threads` (
			`searchid` int(10) unsigned NOT NULL,
			`threadid` int(10) unsigned NOT NULL,
			`score` double unsigned NOT NULL,
		KEY `searchid` (`searchid`,`threadid`)
		) ENGINE=MyISAM DEFAULT CHARSET=utf8');
	}

private function updateMarkup()
	{
	$totalPosts = $this->DB->getColumn
		('SELECT
			COUNT(*)
		FROM
			posts
		');
	$posts = $this->DB->getRowSet
		('
		SELECT
			id,
			text
		FROM
			posts
		');
	$update = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			text = ?
		WHERE
			id = ?
		');

	$brokenPosts = array();

	$i = 0;
	foreach ($posts as $post)
		{
		$i++;
		if ($i % 1000 == 0)
			{
			echo "Processing $i of $totalPosts...\t";
			}
		$text = $this->OldUnMarkup->fromHtml($post['text']);
		
		$text = str_replace('<angel>', '0:-)', $text);
		$text = str_replace('<blank>', ':-|', $text);
		$text = str_replace('', '€', $text);
		$text = str_replace('', '€', $text);

		try
			{
			$text = $this->Markup->toHtml($text);
			}
		catch (RuntimeException $e)
			{
			$brokenPosts[] = $post['id'];
			AdminFunctions::delPost($post['id']);
			continue;
			}

		$update->bindString($text);
		$update->bindInteger($post['id']);

		try
			{
			$update->execute();
			}
		catch (DBException $e)
			{
			$brokenPosts[] = $post['id'];
			AdminFunctions::delPost($post['id']);
			continue;
			}
		
		if ($i % 1000 == 0)
			{
			echo "done\n";
			}
		}

	$update->close();
	
	print_r($brokenPosts);
	}

}

class OldUnMarkup extends Modul {

private $sep;
private $sepc;
private $Stack;

function __construct()
	{
	$this->sep 	= chr(28);

	$this->Stack = new Stack();
	}


public function fromHtml($text)
	{
	if (empty($text))
		{
		return '';
		}

	/** LL3.1-Kompatibilität */
	$text = str_replace('<br />', "\n", $text);

	$text = preg_replace('/<span class="\w+?">/', '', $text);
	$text = str_replace('</span>', '', $text);

	$text = preg_replace_callback('#<h[1-6]>(.+?)</h([1-6])>#', array($this, 'unmakeHeading'), $text);

	$text = preg_replace('#<cite>(.+?)</cite><blockquote><div>#', '<quote $1>', $text);
	$text = str_replace('<blockquote><div>', '<quote>', $text);
	$text = str_replace('</div></blockquote>', '</quote>', $text);

	$text = str_replace('<em>', "''", $text);
	$text = str_replace('</em>', "''", $text);

	$text = str_replace('<strong>', "'''", $text);
	$text = str_replace('</strong>', "'''", $text);

	$text = str_replace('<hr />', "----\n\n", $text);

	$text = str_replace('<q>', '"', $text);
	$text = str_replace('</q>', '"', $text);

	$text = str_replace('<span><del>', '--', $text);
	$text = str_replace('</del>', '--', $text);

	$text = str_replace('<span><ins>', '++', $text);
	$text = str_replace('</ins>', '++', $text);

	$text = preg_replace('#<a href="mailto:(.+?)">.+?</a>#', '$1', $text);


	$text = preg_replace_callback('#<!-- numbered --><a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>\[\d+?\]</a><!-- /numbered -->#', array($this, 'unmakeNumberedLink'), $text);

	$text = preg_replace_callback('#<!-- cutted --><a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>.+?</a><!-- /cutted -->#', array($this, 'unmakeCuttedLink'), $text);

	$text = preg_replace_callback('#<a href="(.+?)"(?: onclick="return !window\.open\(this\.href\);" rel="nofollow" class="extlink"| class="link")>(.+?)</a>#', array($this, 'unmakeLink'), $text);


	$text = preg_replace_callback('#<img src="images/smilies/\w+.gif" alt="(\w+)" class="smiley" />#',array($this, 'unmakeSmiley'), $text);

	$text = preg_replace_callback('#<img src="images/smilies/extra/\w+.gif" alt="(\w+)" class="smiley" />#',array($this, 'unmakeExtraSmiley'), $text);

	$text = preg_replace_callback('#<a href="(?:.+?)GetImage(?:.+?)url=(.+?)" onclick="return !window\.open\(this\.href\);" rel="nofollow"><img src="(?:.+?)" alt="" class="image" /></a>#', array($this, 'urldecode'), $text);

	$text = preg_replace_callback('#<ul>.+</ul>#m', array($this, 'unmakeList'), $text);

	/*
	Jetzt schreiben wir wieder alle gefundenen Tags zurück
	*/
	while ($this->Stack->hasNext())
		{
		$text = str_replace
			(
			$this->sep.$this->Stack->lastID().$this->sep,
			$this->Stack->pop(),
			$text
			);
		}

	$text = unhtmlspecialchars($text);

	return $text;
	}

private function urldecode($matches)
	{
	return urldecode($matches[1]);
	}

private function unmakeHeading($matches)
	{
	return str_repeat('!', $matches[2]).$matches[1];
	}

private function unmakeLocalUrl($url)
	{
	if (empty($url) || strpos($url, '?') === 0 || strpos($url, '/') === 0)
		{
		return 'http://forum.archlinux.de'
			.(strpos($url, '?') === 0 ? '/': '').$url;
		}
	else
		{
		return $url;
		}
	}

private function unmakeLink($matches)
	{
	$url = $matches[1];
	$name = $matches[2];

	$this->Stack->push( '<a href="'.$this->unmakeLocalUrl($url).'">'.$name.'</a>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeNumberedLink($matches)
	{
	$url = $matches[1];
	$this->Stack->push($this->unmakeLocalUrl($url));

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeCuttedLink($matches)
	{
	$url = $matches[1];
	$this->Stack->push($this->unmakeLocalUrl($url));

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeSmiley($matches)
	{
	/** TODO: Stack verwenden */

	switch($matches[1])
		{
		case 'wink' 			: return ';-)';
		case 'grin' 			: return ':-D';
		case 'rolleyes' 		: return ':-)';
		case 'smiley' 			: return ':-)';
		case 'undecided' 		: return ':-/';
		case 'lipsrsealed' 		: return ':-|';
		case 'embarassed' 		: return ':-*)';
		case 'kiss' 			: return ':-*';
		case 'angry' 			: return '>:(';
		case 'tongue' 			: return ':-P';
		case 'cheesy' 			: return 'xD';
		case 'laugh' 			: return 'xD';
		case 'sad' 			: return ':-(';
		case 'shocked' 			: return ':-0';
		case 'cool' 			: return '8)';
		case 'huh' 			: return '???';
		case 'cry' 			: return ':\'(';
		default 			: return $matches[1];
		}
	}

private function unmakeExtraSmiley($matches)
	{
	$this->Stack->push( '<'.$matches[1].'>');

	return $this->sep.$this->Stack->lastID().$this->sep;
	}

private function unmakeList($matches)
	{
	$in = str_replace('</li>', '', $matches[0]);

	$out = '';
	$depth = 0;

	while (preg_match('/<(ul|li|\/ul)>([^<]*)(.*)/sS', $in, $matches))
		{
		switch ($matches[1])
			{
			case 'ul' :
				$depth++;
			break;

			case 'li' :
				$out .= str_repeat('*', $depth).' '.$matches[2]."\n";
			break;

			case '/ul' :
				$depth--;
				if ($depth == 0)
					{
					$out .= $matches[2];
					}
			break;
			}

		$in = $matches[3];
		}

	return $out;
	}

}


Modul::set('OldUnMarkup', new OldUnMarkup());
$rm = new Update();
$rm->run();

?>