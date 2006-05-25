<?php

class Upgrade extends Page{


private $warnings = array();

/**
* @TODO:
* Umbenennungen
*/

public function prepare()
	{
	$this->setValue('title', 'Upgrade');

 	$this->upgradeSchema();
	$this->decodeAttachments();
	$this->upgradeAvatars();
	$this->upgradeThreadSummary();
	$this->rebuildPostings();
	$this->rebuildProfiles();
	$this->rebuildBoardDescription();
	$this->createAttachmentThumbs();


	$this->setValue('body', 'Upgrade war erfolgreich <br />'.implode('<br />', $this->warnings));
	}

private function upgradeSchema()
	{
	$this->DB->execute('ALTER TABLE files RENAME attachments');
	$this->DB->execute('ALTER TABLE post_file RENAME post_attachments');
	$this->DB->execute('ALTER TABLE post_attachments CHANGE `fileid` `attachment_id` MEDIUMINT( 8 ) UNSIGNED NOT NULL DEFAULT 0');

	$this->DB->execute('CREATE TABLE avatars (
		`id` mediumint(8) unsigned NOT NULL auto_increment,
		`name` varchar(200) NOT NULL default \'\',
		`size` mediumint(6) unsigned NOT NULL default 0,
		`type` varchar(100) NOT NULL default \'\',
		`content` mediumblob NOT NULL default \'\',
		PRIMARY KEY  (`id`))');

	$this->DB->execute('CREATE TABLE attachment_thumbnails (
		`id` mediumint(8) unsigned NOT NULL auto_increment,
		`name` varchar(200) NOT NULL default \'\',
		`size` mediumint(6) unsigned NOT NULL default 0,
		`type` varchar(100) NOT NULL default \'\',
		`content` mediumblob NOT NULL default \'\',
		PRIMARY KEY  (`id`))');

// 	$this->DB->execute('CREATE TABLE domain_blacklist (
// 		`domain` varchar(255) NOT NULL,
// 		PRIMARY KEY  (`domain`))');

// 	$this->DB->execute('CREATE TABLE domain_blacklist (
// 		`domain` varchar(255) NOT NULL)');

	$this->DB->execute('ALTER TABLE `threads` ADD `summary` TEXT NOT NULL default \'\'');
// 	$this->DB->execute('ALTER TABLE `users` DROP `svn`');

// 	$this->DB->execute('');
	}

private function upgradeAvatars()
	{
	$avatars = $this->DB->getRowSet
		('
		SELECT
			attachments.content,
			attachments.name,
			attachments.type,
			users.id
		FROM
			attachments,
			users
		WHERE
			users.avatar = attachments.id
		');

	foreach ($avatars as $avatar)
		{
		try
			{
			$content = resizeImage($avatar['content'], $avatar['type'], $this->Settings->getValue('avatar_size'));
			}
		catch (Exception $e)
			{
			$this->warnings[] = 'Avatar: '.$avatar['name'].' ('.$e->getMessage().')';
			$content =  $avatar['content'];
			}

		if (empty($content))
			{
			$content =  $avatar['content'];
			}

		$stm = $this->DB->prepare
			('
			INSERT INTO
				avatars
			SET
				name = ?,
				type = ?,
				size = ?,
				content = ?,
				id = ?'
			);

		$stm->bindString($avatar['name']);
		$stm->bindString($avatar['type']);
		$stm->bindInteger(strlen($content));
		$stm->bindString($content);
		$stm->bindInteger($avatar['id']);
		$stm->execute();

		$stm = $this->DB->prepare
			('
			UPDATE
				users
			SET
				avatar = 1
			WHERE
				id = ?'
			);
		$stm->bindInteger($avatar['id']);
		$stm->execute();
		}

	$this->DB->execute('ALTER TABLE `users` CHANGE `avatar` `avatar` TINYINT( 1 ) UNSIGNED NOT NULL DEFAULT 0');
	}

private function upgradeThreadSummary()
	{
	$threads = $this->DB->getRowSet
		('
		SELECT
			id,
			(SELECT text FROM posts WHERE threadid = threads.id AND dat = threads.firstdate) AS summary
		FROM
			threads
		');

	foreach ($threads as $thread)
		{
		$summary = str_replace('<br />', ' ', $thread['summary']);
		$summary = str_replace("\n", ' ', strip_tags($summary));
		$summary = cutString($summary,  300);

		$stm = $this->DB->prepare
			('
			UPDATE
				threads
			SET
				summary = ?
			WHERE
				id = ?
			');

		$stm->bindString($summary);
		$stm->bindInteger($thread['id']);
		$stm->execute();
		}
	}

private function rebuildPostings()
	{
	$posts = $this->DB->getRowSet
		('
		SELECT
			id,
			text
		FROM
			posts
		');

	foreach ($posts as $post)
		{
		$this->rebuildText($post['text']);

		$stm = $this->DB->prepare
			('
			UPDATE
				posts
			SET
				text = ?
			WHERE
				id = ?
			');
		$stm->bindString($post['text']);
		$stm->bindInteger($post['id']);
		$stm->execute();
		}
	}

private function rebuildText(&$text)
	{
	$text = $this->Markup->toHtml($this->UpgradeUnMarkup->fromHtml($text));
	}

private function rebuildProfiles()
	{
	$profiles = $this->DB->getRowSet
		('
		SELECT
			id,
			text
		FROM
			users
		');

	foreach ($profiles as $profile)
		{
		$this->rebuildText($profile['text']);

		$stm = $this->DB->prepare
			('
			UPDATE
				users
			SET
				text = ?
			WHERE
				id = ?
			');
		$stm->bindString($profile['text']);
		$stm->bindInteger($profile['id']);
		$stm->execute();
		}
	}

private function rebuildBoardDescription()
	{
	$profiles = $this->DB->getRowSet
		('
		SELECT
			id,
			description
		FROM
			boards
		');

	foreach ($profiles as $profile)
		{
		$this->rebuildText($profile['description']);

		$stm = $this->DB->prepare
			('
			UPDATE
				boards
			SET
				description = ?
			WHERE
				id = ?
			');
		$stm->bindString($profile['description']);
		$stm->bindInteger($profile['id']);
		$stm->execute();
		}
	}


private function createAttachmentThumbs()
	{
	$attachments = $this->DB->getRowSet
		('
		SELECT
			id,
			name,
			content,
			type
		FROM
			attachments
		WHERE
			type LIKE \'image/%\'
		');

	foreach ($attachments as $attachment)
		{
		try
			{
			$content = resizeImage($attachment['content'], $attachment['type'], $this->Settings->getValue('thumb_size'));
			}
		catch (Exception $e)
			{
			$this->warnings[] = 'Attachment: '.$attachment['name'].' ('.$e->getMessage().')';
			continue;
			}

		if (empty($content))
			{
			$content =  $attachment['content'];
			}

		$stm = $this->DB->prepare
			('
			INSERT INTO
				attachment_thumbnails
			SET
				name = ?,
				type = ?,
				size = ?,
				content = ?,
				id = ?'
			);

		$stm->bindString($attachment['name']);
		$stm->bindString($attachment['type']);
		$stm->bindInteger(strlen($content));
		$stm->bindString($content);
		$stm->bindInteger($attachment['id']);
		$stm->execute();
		}
	}

private function decodeAttachments()
	{
	$attachments = $this->DB->getRowSet
		('
		SELECT
			id,
			content
		FROM
			attachments
		');

	foreach ($attachments as $attachment)
		{
		$content = gzinflate(substr($attachment['content'], 10));

		$stm = $this->DB->prepare
			('
			UPDATE
				attachments
			SET
				size = ?,
				content = ?
			WHERE
				id = ?'
			);

		$stm->bindInteger(strlen($content));
		$stm->bindString($content);
		$stm->bindInteger($attachment['id']);
		$stm->execute();
		}
	}

}

?>