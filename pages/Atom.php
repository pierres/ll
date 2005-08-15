<?php


class Atom extends Page{

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
				threads.lastusername
			FROM
				threads
			WHERE
				threads.deleted = 0
				AND threads.forumid != 0
			ORDER BY
				threads.id DESC
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
			<entry>
			<title>'.$thread['name'].'</title>
			<link href="http://www.laber-land.de/?page=Postings;id='.$this->Board->getId().';thread='.$thread['id'].';post=-1" />
			<id>'.$thread['id'].'</id>
			<updated>'.date('c', $thread['lastdate']).'</updated>
			<summary></summary>
			<author>
				<name>'.$thread['lastusername'].'</name>
				<uri>http://www.laber-land.de/?page=ShowUser;id='.$this->Board->getId().';user='.$thread['lastuserid'].'</uri>
			</author>
			</entry>
			';
		}

	$this->output =
'<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>'.$this->Board->getName().'</title>
	<logo>http://www.laber-land.de/images/logo.png</logo>
	<id>'.$this->Board->getId().'</id>
	<link href="http://www.laber-land.de/?page=Forums;id='.$this->Board->getId().'" />
	<updated></updated>
	<author>
	<name>Pierre Schmitz</name>
	</author>
	'.$entries.'
</feed>';
	}

public function show()
	{
	$this->Io->setContentType('Content-Type: application/x.atom+xml; charset=UTF-8');
	$this->Io->out($this->output);
	}

}

?>