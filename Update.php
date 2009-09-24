#!/usr/bin/php
<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
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
	$this->updateThreads();
	$this->updateForums();
	$this->updateImages();
	$this->updateAvatars();
	$this->updateAttachements();

	$this->DB->execute('OPTIMIZE TABLE `'.implode('` , `', $tables).'`');
	$this->DB->execute('UNLOCK TABLES');
	}

private function updateDB()
	{
// 	$this->DB->execute('DROP TABLE tags');
// 	$this->DB->execute('ALTER TABLE threads DROP tag');
	$this->DB->execute('ALTER TABLE boards DROP description, DROP admin_name, DROP admin_email, DROP admin_address, DROP admin_tel');
	$this->DB->execute('DROP TABLE plz');
	$this->DB->execute('ALTER TABLE session DROP hidden');
	$this->DB->execute('ALTER TABLE users DROP birthday, DROP location, DROP plz, DROP `text`, DROP hidden');
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

private function updateImages()
	{
	$this->DB->execute('ALTER TABLE images DROP size, DROP thumbsize');
	$totalImages = $this->DB->getColumn
		('SELECT
			COUNT(*)
		FROM
			images
		');
	$images = $this->DB->getRowSet
		('
		SELECT
			url,
			content
		FROM
			images
		');
	$update = $this->DB->prepare
		('
		UPDATE
			images
		SET
			type = ?,
			thumbcontent = ?
		WHERE
			url = ?
		');

	$i = 0;
	foreach ($images as $image)
		{
		$i++;
		if ($i % 5 == 0)
			{
			echo "Processing $i of $totalImages...\t";
			}
		
		$type = $this->getTypeFromContent($image['content']);
		
		if (	strpos($type, 'image/jpeg') !== 0 &&
			strpos($type, 'image/png') !== 0 &&
			strpos($type, 'image/gif') !== 0)
			{
			throw new RuntimeException('no image type', $i);
			}

		try
			{
			$thumbcontent = resizeImage($image['content'], $type, $this->Settings->getValue('thumb_size'));
			}
		catch (Exception $e)
			{
			$thumbcontent = '';
			}
		
		$update->bindString($type);
		$update->bindString($thumbcontent);
		$update->bindString($image['url']);
		$update->execute();
		
		if ($i % 5 == 0)
			{
			echo "done\n";
			}
		}

	$update->close();
	}

private function updateAvatars()
	{
	$this->DB->execute('ALTER TABLE avatars DROP size, DROP name');
	$totalAvatars = $this->DB->getColumn
		('SELECT
			COUNT(*)
		FROM
			avatars
		');
	$avatars = $this->DB->getRowSet
		('
		SELECT
			id,
			content
		FROM
			avatars
		');
	$update = $this->DB->prepare
		('
		UPDATE
			avatars
		SET
			type = ?,
			content = ?
		WHERE
			id = ?
		');

	$i = 0;
	foreach ($avatars as $avatar)
		{
		$i++;
		if ($i % 5 == 0)
			{
			echo "Processing $i of $totalAvatars...\t";
			}
		
		$type = $this->getTypeFromContent($avatar['content']);
		
		if (	strpos($type, 'image/jpeg') !== 0 &&
			strpos($type, 'image/png') !== 0 &&
			strpos($type, 'image/gif') !== 0)
			{
			throw new RuntimeException('no image type', $i);
			}

		try
			{
			$content = resizeImage($avatar['content'], $type, $this->Settings->getValue('avatar_size'));
			}
		catch (Exception $e)
			{
			$content = $avatar['content'];
			}
		
		$update->bindString($type);
		$update->bindString($content);
		$update->bindInteger($avatar['id']);
		$update->execute();
		
		if ($i % 5 == 0)
			{
			echo "done\n";
			}
		}

	$update->close();
	}

private function updateAttachements()
	{
	$this->DB->execute('ALTER TABLE attachments DROP size');
	$this->DB->execute('ALTER TABLE attachment_thumbnails DROP size, DROP name, DROP type');
	$this->DB->execute('DELETE FROM attachment_thumbnails');
	$totalImages = $this->DB->getColumn
		('SELECT
			COUNT(*)
		FROM
			attachments
		');
	$images = $this->DB->getRowSet
		('
		SELECT
			id,
			content
		FROM
			attachments
		');
	$update = $this->DB->prepare
		('
		UPDATE
			attachments
		SET
			type = ?
		WHERE
			id = ?
		');
	$updateThumbs = $this->DB->prepare
		('
		INSERT INTO
			attachment_thumbnails
		SET
			content = ?,
			id = ?
		');

	$i = 0;
	foreach ($images as $image)
		{
		$i++;
		if ($i % 5 == 0)
			{
			echo "Processing $i of $totalImages...\t";
			}
		
		$type = $this->getTypeFromContent($image['content']);
		
		if (	strpos($type, 'image/jpeg') === 0 ||
			strpos($type, 'image/png') === 0 ||
			strpos($type, 'image/gif') === 0)
			{
			try
				{
				$thumbcontent = resizeImage($image['content'], $type, $this->Settings->getValue('thumb_size'));
				$updateThumbs->bindString($thumbcontent);
				$updateThumbs->bindInteger($image['id']);
				$updateThumbs->execute();
				}
			catch (RuntimeException $e)
				{
				}
			catch (Exception $e)
				{
				echo "\n", 'Removing file ', $image['id'], "\n";
				$this->delFile($image['id']);
				continue;
				}
			}
		
		$update->bindString($type);
		$update->bindInteger($image['id']);
		$update->execute();
		
		if ($i % 5 == 0)
			{
			echo "done\n";
			}
		}

	$update->close();
	$updateThumbs->close();
	}

protected function delFile($id)
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			attachments
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		DELETE FROM
			attachment_thumbnails
		WHERE
			id = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				postid
			FROM
				post_attachments
			WHERE
				attachment_id = ?'
			);
		$stm->bindInteger($id);

		foreach($stm->getColumnSet() as $post)
			{
			// Das ist also die letzte Datei für diesen Beitrag ...
			$stm2 = $this->DB->prepare
				('
				SELECT
					COUNT(*)
				FROM
					post_attachments
				WHERE
					postid = ?'
				);
			$stm2->bindInteger($post);

			if ($stm2->getColumn() == 1)
				{
				$stm3 = $this->DB->prepare
					('
					UPDATE
						posts
					SET
						file = 0
					WHERE
						id = ?'
					);
				$stm3->bindInteger($post);
				$stm3->execute();
				$stm3->close();
				}
			$stm2->close();
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		}

	$stm = $this->DB->prepare
		('
		DELETE FROM
			post_attachments
		WHERE
			attachment_id = ?'
		);
	$stm->bindInteger($id);
	$stm->execute();
	$stm->close();
	}

private function getTypeFromContent($content)
	{
	if (function_exists('finfo_open'))
		{
		$finfo = finfo_open(FILEINFO_MIME);
		$type = finfo_buffer($finfo, $content);
		finfo_close($finfo);
		/** @TODO: review with php 5.3 */
		// new version produces strings like 'image/png; charset=binary'
		// we only need the first part
		$type = strtok($type, ';');
		}
	else
		{
		throw new FileException('No fileinfo module found');
		}

	return $type;
	}

private function updateMarkup()
	{
	$this->DB->execute('ALTER TABLE posts DROP smilies');
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
		$text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
		try
			{
			$text = $this->Markup->toHtml($text);
			}
		catch (RuntimeException $e)
			{
			try
				{
				$text = str_replace('<pre>', ' ', $text);
				$text = str_replace('</pre>', ' ', $text);
				$text = $this->Markup->toHtml('<pre>'.$text.'</pre>');
				}
			catch (RuntimeException $e)
				{
				$brokenPosts[] = $post['id'];
				AdminFunctions::delPost($post['id']);
				continue;
				}
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

private function updateThreads()
	{
	$totalThreads = $this->DB->getColumn
		('SELECT
			COUNT(*)
		FROM
			threads
		');
	$threads = $this->DB->getColumnSet
		('
		SELECT
			id
		FROM
			threads
		');

	$i = 0;
	foreach ($threads as $thread)
		{
		$i++;
		if ($i % 100 == 0)
			{
			echo "Processing $i of $totalThreads...\t";
			}

		AdminFunctions::updateThread($thread);

		if ($i % 100 == 0)
			{
			echo "done\n";
			}
		}
	}

private function updateForums()
	{
	$totalForums = $this->DB->getColumn
		('SELECT
			COUNT(*)
		FROM
			forums
		');
	$forums = $this->DB->getColumnSet
		('
		SELECT
			id
		FROM
			forums
		');

	$i = 0;
	foreach ($forums as $forum)
		{
		$i++;
		if ($i % 10 == 0)
			{
			echo "Processing $i of $totalForums...\t";
			}

		AdminFunctions::updateForum($forum);

		if ($i % 10 == 0)
			{
			echo "done\n";
			}
		}
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

	$text = str_replace('<hr />', "\n----\n\n", $text);

	$text = str_replace('<q>', '"', $text);
	$text = str_replace('</q>', '"', $text);

	$text = str_replace('<span><del>', '--', $text);
	$text = str_replace('</del>', '--', $text);

	$text = str_replace('<span><ins>', '++', $text);
	$text = str_replace('</ins>', '++', $text);

	$text = str_replace('<pre>', '<code>', $text);
	$text = str_replace('</pre>', '</code>', $text);

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
	return "'''".$matches[1]."'''\n\n";
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
	
	if ($url != $name)
		{
		$this->Stack->push('<a href="'.$this->unmakeLocalUrl($url).'">'.$name.'</a>');
		}
	else
		{
		$this->Stack->push($this->unmakeLocalUrl($url));		
		}

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
