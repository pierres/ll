<?php

/** TODO: Sicherheit und Zugriffsrechte */


class NewPost extends Form {


protected $text 		= '';
protected $thread		= 0;
protected $forum		= 0;

protected $time 		= 0;
protected $smilies 	= true;
protected $title 		= 'Beitrag schreiben';

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
		/** FIXME: position of preview-window is not allways optimal */
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

	$this->addOutput('<a href="html/LLCodes.html" onclick="return !window.open(this.href,\'_blank\',\'dependent=yes,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes,width=610\');" rel="nofollow" class="link"><span class="button">LL-Codes</span></a><br /><br />');

	$this->addCheckbox('smilies', 'grafische Smilies', $this->smilies);

	$this->setFile();

	if (!empty($this->thread))
		{
		$this->addOutput($this->getLastPosts());
		}
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
				$stm = $this->DB->prepare
					('
					SELECT
						id,
						name,
						size
					FROM
						attachments
					WHERE
						userid = ?
					ORDER BY
						id DESC
					');
				$stm->bindInteger($this->User->getId());
				$files = $stm->getRowSet();
				}
			catch (DBNoDataException $e)
				{
				$stm->close();
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
			$stm->close();

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

protected function getLastPosts()
	{
	$posts = '<div class="frame" style="padding:5px;width:500px;height:200px;overflow:auto;">';

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				posts.userid,
				posts.username,
				users.name,
				posts.text
			FROM
				posts LEFT JOIN users
					ON posts.userid = users.id
			WHERE
				posts.threadid = ?
				AND posts.deleted = 0
			ORDER BY
				posts.id DESC
			LIMIT 5
			');
		$stm->bindInteger($this->thread);

		foreach ($stm->getRowSet() as $post)
			{
			$poster = (!empty($post['userid']) ? '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$post['userid'].'">'.$post['name'].'</a>' : $post['username']);

			$posts .= '<cite>'.$poster.'</cite><blockquote>'.$post['text'].'</blockquote>';
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	return $posts.'</div>';
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

		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				attachments
			WHERE
				id = ?
				AND userid = ?'
			);

		$stm2 = $this->DB->prepare
			('
			INSERT INTO
				post_attachments
			SET
				postid = ?,
				fileid = ?'
			);
		foreach($files as $file => $blubb)
			{
			try
				{
				$stm->bindInteger($file);
				$stm->bindInteger($this->User->getId());
				$stm->getColumn();
				}
			catch (DBNoDataException $e)
				{
				continue;
				}

			$stm2->bindInteger($postid);
			$stm2->bindInteger($file);
			$stm2->execute();

			$success = true;
			}
		$stm->close();
		$stm2->close();

		if ($success)
			{
			$stm = $this->DB->prepare
				('
				UPDATE
					posts
				SET
					file = 1
				WHERE
					id = ?'
				);
			$stm->bindInteger($postid);
			$stm->execute();
			$stm->close();
			}
		}
	}

protected function checkNewFile()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$this->file = $this->Io->getUploadedFile('file');
			}
		catch (IoException $e)
			{
			return;
			}

		if ($this->file['size'] >= $this->Settings->getValue('file_size'))
			{
			$this->showWarning('Datei ist zu groß!');
			}

		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					COUNT(*) AS files,
					SUM(size) AS quota
				FROM
					attachments
				WHERE
					userid = ?'
				);
			$stm->bindInteger($this->User->getId());
			$data = $stm->getRow();
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			return;
			}

		if ($data['quota'] + $this->file['size'] >=  $this->Settings->getValue('quota'))
			{
			$this->showWarning('Dein Speicherplatz ist voll!');
			}

		if ($data['files'] + 1 >=  $this->Settings->getValue('files'))
			{
			$this->showWarning('Du hast zu viele Dateien gespeichert!');
			}
		}
	}

protected function sendNewFile($files)
	{
	if ($this->User->isOnline() && !empty($this->file))
		{
		$content = file_get_contents($this->file['tmp_name']);

		$stm = $this->DB->prepare
			('
			INSERT INTO
				attachments
			SET
				name = ?,
				type = ?,
				size = ?,
				content = ?,
				userid = ?,
				uploaded = ?'
			);
		$stm->bindString(htmlspecialchars($this->file['name']));
		$stm->bindString($this->file['type']);
		$stm->bindInteger(strlen($content));
		$stm->bindString($content);
		$stm->bindInteger($this->User->getId());
		$stm->bindInteger(time());

		$stm->execute();
		$stm->close();

		$files[$this->DB->getInsertId()] = '';

		unlink($this->file['tmp_name']);

		if (strpos($file['type'], 'image/jpeg') === 0 ||
			strpos($file['type'], 'image/pjpeg') === 0 ||
			strpos($file['type'], 'image/png') === 0 ||
			strpos($file['type'], 'image/gif') === 0)
			{
			try
				{
				$thumbcontent = resizeImage($content, $this->file['type'], $this->Settings->getValue('thumb_size'));
				}
			catch (Exception $e)
				{
				return $files;
				}

			$stm = $this->DB->prepare
				('
				INSERT INTO
					attachment_thumbnails
				SET
					id = ?,
					size = ?,
					content = ?'
				);
			$stm->bindInteger($this->DB->getInsertId());
			$stm->bindInteger(strlen($thumbcontent));
			$stm->bindString($thumbcontent);

			$stm->execute();
			}
		}

	return $files;
	}

protected function checkInput()
	{
	try
		{
		$stm = $this->DB->prepare
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
				AND id = ?'
			);
		$stm->bindInteger($this->Io->getInt('thread'));
		$data = $stm->getRow();
		$stm->close();
		}
	catch (IoException $e)
		{
		$stm->close();
		$this->showFailure('Kein Thema angegeben!');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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
			$stm = $this->DB->prepare
				('
				SELECT
					id,
					name
				FROM
					users
				WHERE
					name = ?'
				);
			$stm->bindString($this->Io->getHtml('name'));
			$user = $stm->getRow();
			$stm->close();

			$this->showWarning('Der Name <strong><a href="?page=ShowUser;user='.$user['id'].';id='.$this->Board->getId().'">'.$user['name'].'</a></strong> wurde bereits registriert. <strong><a href="?page=Login;id='.$this->Board->getId().';name='.urlencode($this->Io->getHtml('name')).'">Melde Dich an</a></strong>, falls dies Dein Benutzer-Konto ist.');
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			}
		}

	if (!$this->User->isOnline())
		{
		$AntiSpam = new AntiSpam($this->text);
		if ($AntiSpam->isSpam())
			{
			unset($AntiSpam);
			$this->showFailure('Dein Beitrag wurde als Spam eingestuft. Falls dies eine Falschmeldung ist, <a href="?page=Contact;id='.$this->Board->getId().'" class="link">benachrichtige uns</a> bitte. Siehe auch: <a href="?page=DomainBlacklist;id='.$this->Board->getId().'" class="link">Gesperrte Domains</a>');
			}
		}

	$this->checkNewFile();

	$this->Markup->enableSmilies($this->smilies);
	$this->text = $this->Markup->toHtml($this->text);
	// BugFix for Bug#1
	if ($length = strlen($this->text) > 65536)
		{
		$this->showWarning('Der Text ist '.($length-65536).' Zeichen zu lang!');
		}
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

		$stm = $this->DB->prepare
			('
			UPDATE
				users
			SET
				posts = posts + 1,
				lastpost = ?
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->time);
		$stm->bindInteger($userid);
		$stm->execute();
		$stm->close();
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

	$stm = $this->DB->prepare
		('
		INSERT INTO
			posts
		SET
			threadid = ?,
			userid = ?,
			username = ?,
			text = ?,
			dat = ?,
			smilies = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($userid);
	$stm->bindString($username);
	$stm->bindString($this->text);
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->smilies ? 1 : 0);

	$stm->execute();
	$stm->close();

	$this->sendFile($this->DB->getInsertId());

	$this->updateThread();
	$this->updateForum();

	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			posts = posts + 1,
			lastpost = ?
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

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
		$stm = $this->DB->prepare
			('
			SELECT
				threads.name AS thread,
				forums.name AS forum,
				forums.id AS forumid
			FROM
				threads,
				forums
			WHERE
				threads.id = ?
				AND threads.forumid = forums.id
			');
		$stm->bindInteger($this->thread);
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
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