<?php


class GetRecent extends GetFile{

public function prepare()
	{
	$this->initDB();
	}

public function show()
	{
	$entries = '';

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				threads.id,
				threads.name,
				threads.lastdate,
				threads.lastusername,
				threads.lastuserid,
				threads.summary
			FROM
				threads,
				forum_cat,
				cats
			WHERE
				threads.deleted = 0
				AND forum_cat.forumid = threads.forumid
				AND forum_cat.catid = cats.id
				AND cats.boardid = ?
			ORDER BY
				threads.lastdate DESC
			LIMIT
				25
			');
		$stm->bindInteger($this->Board->getId());

		$lastdate = 0;

		foreach($stm->getRowSet() as $thread)
			{
			if ($thread['lastdate'] > $lastdate)
				{
				$lastdate = $thread['lastdate'];
				}

			$entries .=
			'
			<entry>
				<title>'.$thread['name'].'</title>
				<link href="'.$this->Io->getURL().'?page=Postings;id='.$this->Board->getId().';thread='.$thread['id'].';post=-1" />
				<id>'.$thread['id'].'</id>
				<updated>'.date('c', $thread['lastdate']).'</updated>
				<summary>'.$thread['summary'].'</summary>
				<author>
					<name>'.$thread['lastusername'].'</name>
					<uri>'.$this->Io->getURL().'?page=ShowUser;id='.$this->Board->getId().';user='.$thread['lastuserid'].'</uri>
				</author>
			</entry>
			';
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}


	$content =
'<?xml version="1.0" encoding="utf-8"?>
<feed xmlns="http://www.w3.org/2005/Atom">
	<title>'.$this->Board->getName().'</title>
	<id>'.$this->Board->getId().'</id>
	<link href="'.$this->Io->getURL().'?page=Forums;id='.$this->Board->getId().'" />
	<updated>'.date('c', $lastdate).'</updated>
	<author>
		<name>Pierre Schmitz</name>
	</author>
	'.$entries.'
</feed>';

	$this->sendInlineFile('application/x.atom+xml; charset=UTF-8', 'recent.xml', strlen($content), $content);
	}

}

?>