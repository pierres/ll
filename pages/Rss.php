<?php


class Rss extends Page{

private $output = '';
private $threads = array();


public function prepare()
	{
	try
		{
		$this->threads = $this->Sql->fetch
			('
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.lastuserid,
				threads.lastusername,
				posts.text
			FROM
				threads
					LEFT JOIN posts
					ON posts.threadid = threads.id AND posts.dat = threads.lastdate
			WHERE
				threads.deleted = 0
				AND threads.forumid != 0
			ORDER BY
				posts.id DESC
			LIMIT
				25
			');
		}
	catch (SqlNoDataException $e)
		{
		$this->threads = array();
		}

	$this->buildAtom();
	}

private function buildAtom()
	{
	$entries = '';
	foreach($this->threads as $thread)
		{
		$entries .=
			'
			<item>
			<title>'.$thread['name'].'</title>
			<link>http://www.laber-land.de/?page=Postings;id='.$this->Board->getId().';thread='.$thread['id'].';post=-1</link>
			<guid>'.$thread['id'].'</guid>
			<pubDate>'.date('r', $thread['lastdate']).'</pubDate>
			<author>'.$thread['lastusername'].'</author>
			<description>'.$thread['text'].'</description>
			</item>
			';
		}

	$this->output =
'<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
	<channel>
	<title>'.$this->Board->getName().'</title>
	<image><url>http://www.laber-land.de/images/logo.png</url></image>
	<link>http://www.laber-land.de/?page=Forums;id='.$this->Board->getId().'</link>
	'.$entries.'
	</channel>
</rss>';
	}

public function show()
	{
	$this->Io->setContentType('Content-Type: application/rss+xml; charset=UTF-8');
	$this->Io->out($this->output);
	}

}

?>