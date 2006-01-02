<?php

/** TODO: Sicherheit und Zugriffsrechte */


class NewPost extends Form {


protected $text 	= '';
protected $thread	= 0;
protected $forum	= 0;

protected $time 	= 0;
protected $smilies 	= true;
protected $title 	= 'Beitrag schreiben';

protected $file		= array();


protected function setForm()
	{
	$this->checkInput();
	$this->checkAccess();

	$this->setValue('title', $this->title);
	$this->time = time();

	$this->addSubmit('Abschicken');
	$this->addButton('preview', 'Vorschau');


	if ($this->Io->isRequest('preview') && !$this->Io->isEmpty('text'))
		{
		$this->text = $this->Io->getString('text');
		$this->Markup->enableSmilies($this->Io->isRequest('smilies'));

		$this->addElement('previewwindow',
		'<div class="preview">'.$this->Markup->toHtml($this->text).'</div>');
		}

	if (!$this->User->isOnline())
		{
		$this->addText('name', 'Dein Name');
		$this->setLength('name', 3,25);
		}

	$this->addTextarea('text', 'Deine Nachricht', $this->text);
	$this->requires('text');
	$this->setLength('text', 3, 65536);

	$this->addCheckbox('smilies', 'grafische Smilies', $this->smilies);

	$this->setFile();
	}

protected function setFile()
	{
	if ($this->User->isOnline())
		{
		if (($this->Io->isRequest('addfile')) && !$this->Io->isRequest('nofile'))
			{
			$this->addButton('nofile', 'keine Dateien');

			try
				{
				$files = $this->Sql->fetch
					('
					SELECT
						id,
						name,
						size
					FROM
						files
					WHERE
						userid = '.$this->User->getId().'
					ORDER BY
						id DESC
					');
				}
			catch (SqlNoDataException $e)
				{
				$files = array();
				}

			$this->addOutput('<br />Dateien auswählen:<br /><table class="frame" style="margin:10px;font-size:9px;">');

			foreach ($files as $file)
				{
				$this->addOutput('<tr><td style="padding:5px;">');
				$this->addCheckbox('files['.$file['id'].']',
				'<a class="link" onclick="openLink(this)" href="?page=GetFile;file='.$file['id'].'">'.$file['name'].'</a>');
				$this->addOutput('</td><td style="text-align:right;padding:5px;vertical-align:bottom;">'.round($file['size'] / 1024, 2).' KByte</td></tr>');
				}

			$this->addOutput('</table><br />');
			$this->addFile('file', 'Neue Datei hinzufügen');
			$this->addOutput('<br />');

			$this->addHidden('addfile', 1);
			}
		else
			{
			$this->addButton('addfile', 'Dateien');
			}
		}
	}

protected function sendFile($postid)
	{
	if($this->User->isOnline() && $this->Io->isRequest('addfile'))
		{
		$files = $this->Io->getArray();

		$files = $this->sendNewFile($files);

		if (empty($files))
			{
			return;
			}

		$success = false;

		foreach($files as $file => $blubb)
			{
			try
				{
				$this->Sql->fetchValue
					('
					SELECT
						id
					FROM
						files
					WHERE
						id = '.intval($file).'
						AND userid = '.$this->User->getId()
					);
				}
			catch (SqlNoDataException $e)
				{
				continue;
				}

			$this->Sql->query
				('
				INSERT INTO
					post_file
				SET
					postid = '.$postid.',
					fileid = '.intval($file)
				);

			$success = true;
			}

		if ($success)
			{
			$this->Sql->query
				('
				UPDATE
					posts
				SET
					file = 1
				WHERE
					id ='.$postid
				);
			}
		}
	}

protected function checkNewFile()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$this->file = $this->Io->getFile('file');
			}
		catch (IoException $e)
			{
			return;
			//$this->showWarning('Datei wurde nicht hochgeladen!');
			}

		if ($this->file['size'] >= Settings::FILE_SIZE)
			{
			$this->showWarning('Datei ist zu groß!');
			}

		$data = $this->Sql->fetchRow
			('
			SELECT
				COUNT(*) AS files,
				SUM(size) AS quota
			FROM
				files
			WHERE
				userid = '.$this->User->getId()
			);

		if ($data['quota'] + $this->file['size'] >=  Settings::QUOTA)
			{
			$this->showWarning('Dein Speicherplatz ist voll!');
			}

		if ($data['files'] + 1 >=  Settings::FILES)
			{
			$this->showWarning('Du hast zu viele Dateien gespeichert!');
			}
		}
	}

protected function sendNewFile($files)
	{
	if ($this->User->isOnline() && !empty($this->file))
		{
		$content = gzencode(file_get_contents($this->file['tmp_name']), 9);

		$this->Sql->query
			('
			INSERT INTO
				files
			SET
				name = \''.$this->Sql->formatString($this->file['name']).'\',
				type = \''.$this->Sql->formatString($this->file['type']).'\',
				size = '.strlen($content).',
				content = \''.$this->Sql->escapeString($content).'\',
				userid = '.$this->User->getId().',
				uploaded = '.time()
			);

		$files[$this->Sql->insertId()] = '';

		unlink($this->file['tmp_name']);
		}

	return $files;
	}

protected function checkInput()
	{
	try
		{
		$data = $this->Sql->fetchRow
			('
			SELECT
				id,
				forumid,
				closed
			FROM
				threads
			WHERE
				forumid != 0
				AND deleted = 0
				AND id = '.$this->Io->getInt('thread')
			);
		}
	catch (IoException $e)
		{
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (SqlNoDataException $e)
		{
		$this->showFailure('Thema nicht gefunden!');
		}

	if ($data['closed'] != 0)
		{
		$this->showFailure('Thema wurde geschlossen!');
		}

	$this->thread = $data['id'];
	$this->forum = $data['forumid'];

	$this->addHidden('thread', $this->thread);
	}

protected function checkForm()
	{
	$this->smilies = $this->Io->isRequest('smilies');
	$this->text = $this->Io->getString('text');

	if (!$this->User->isOnline() && !$this->Io->isEmpty('name'))
		{
		try
			{
			$user = $this->Sql->fetchRow
				('
				SELECT
					id,
					name
				FROM
					users
				WHERE
					name = \''.$this->Sql->escapeString($this->Io->getHtml('name')).'\''
				);

			$this->showWarning('Der Name <strong><a href="?page=ShowUser;user='.$user['id'].';id='.$this->Board->getId().'">'.$user['name'].'</a></strong> wurde bereits registriert. <strong><a href="?page=Login;id='.$this->Board->getId().';name='.urlencode($this->Io->getHtml('name')).'">Melde Dich an</a></strong>, falls dies Dein Benutzer-Konto ist.');
			}
		catch (SqlNoDataException $e)
			{
			}
		}

	$this->checkNewFile();
	}

protected function checkAccess()
	{
	/** Privater Thread -> Prüfung */
	}

protected function sendForm()
	{
	if($this->User->isOnline())
		{
		$username = $this->User->getName();
		$userid = $this->User->getId();

		$this->Sql->query(
			'
			UPDATE
				users
			SET
				posts = posts + 1,
				lastpost = '.$this->time.'
			WHERE
				id = '.$userid
			);
		}
	else
		{
		if (!$this->Io->isEmpty('name'))
			{
			$username = $this->Io->getHtml('name');
			}
		else
			{
			$username = 'Gast';
			}

		$userid = 0;
		}

	$this->Markup->enableSmilies($this->smilies);
	$this->text = $this->Markup->toHtml($this->text);

	$this->Sql->query
		('
		INSERT INTO
			posts
		SET
			threadid = '.$this->thread.',
			userid = '.$userid.',
			username = \''.$this->Sql->escapeString($username).'\',
			text = \''.$this->Sql->escapeString($this->text).'\',
			dat = '.$this->time.',
			smilies ='.($this->smilies ? 1 : 0)
		);

	$this->sendFile($this->Sql->insertId());

	$this->UpdateThread();
	$this->updateForum();

	$this->Sql->query(
		'
		UPDATE
			boards
		SET
			posts = posts + 1,
			lastpost = '.$this->time.'
		WHERE
			id = '.$this->Board->getId()
		);

	$this->Log->insert($this->thread, $this->time);

	$this->redirect();
	}

protected function updateThread()
	{
	AdminFunctions::updateThread($this->thread);
	}

protected function updateForum()
	{
	AdminFunctions::updateForum($this->forum);
	}

protected function redirect()
	{
	try
		{
		$data = $this->Sql->fetchRow
			('
			SELECT
				threads.name AS thread,
				forums.name AS forum,
				forums.id AS forumid
			FROM
				threads,
				forums
			WHERE
				threads.id = '.$this->thread.'
				AND threads.forumid = forums.id
			');
		}
	catch (SqlNoDataException $e)
		{
		$data['thread'] = '';
		$data['forum'] = '';
		$data['forumid'] = 0;
		}

	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Beitrag geschrieben
				</td>
			</tr>
			<tr>
				<td class="main">
					Wohin darf es nun gehen?
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=Postings;id='.$this->Board->getId().';thread='.$this->thread.';post=-1#last">&#187; zurück zum Thema &quot;<em>'.$data['thread'].'</em>&quot;</a>
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=Threads;id='.$this->Board->getId().';forum='.$data['forumid'].'">&#187; zum Forum &quot;<em>'.$data['forum'].'</em>&quot;</a>
				</td>
			</tr>
			<tr>
				<td class="main">
					<a href="?page=Forums;id='.$this->Board->getId().'">&#187; zum Board &quot;<em>'.$this->Board->getName().'</em>&quot;</a>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Beitrag geschrieben');
	$this->setValue('body', $body);
	}

}


?>