<?php


class NewPost extends Form {


protected $text 	= '';
protected $thread	= 0;
protected $forum	= 0;

protected $time 	= 0;
protected $smilies 	= true;
protected $title 	= 'Beitrag schreiben';

protected $file		= array();

protected $counter 	= 0;


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
		/** TODO: position of preview-window is not allways optimal */
		$this->addElement('previewwindow',
		'<div class="preview">'.$this->Markup->toHtml($this->text).'</div>');
		}

	if (!$this->User->isOnline())
		{
		$this->addText('name', 'Dein Name');
		$this->setLength('name', 3,25);
		$this->requires('name');
		}

	$this->addTextarea('text', 'Deine Nachricht', $this->text);
	$this->requires('text');
	$this->setLength('text', 3, 65536);

	$this->addOutput('<a href="html/LLCodes.html" onclick="return !window.open(this.href,\'_blank\',\'dependent=yes,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes,width=610\');" rel="nofollow" class="link"><span class="button">LL-Codes</span></a> <a href="html/Smilies.html" onclick="return !window.open(this.href,\'_blank\',\'dependent=yes,location=no,menubar=no,status=no,toolbar=no,scrollbars=yes,width=610\');" rel="nofollow" class="link"><span class="button">Smilies</span></a><br /><br />');

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
						size,
						type
					FROM
						attachments
					WHERE
						userid = ?
					ORDER BY
						id DESC
					');
				$stm->bindInteger($this->User->getId());
				$files = $stm->getRowSet();

				$this->addOutput('<br />Dateien auswählen:<br /><table class="frame" style="margin:10px;font-size:9px;">');

				foreach ($files as $file)
					{
					if (strpos($file['type'], 'image/jpeg') === 0 ||
						strpos($file['type'], 'image/pjpeg') === 0 ||
						strpos($file['type'], 'image/png') === 0 ||
						strpos($file['type'], 'image/gif') === 0)
						{
						$hover = '  onmouseover="javascript:document.getElementById(\'thumb'.$file['id'].'\').style.visibility=\'visible\'"
							onmouseout="javascript:document.getElementById(\'thumb'.$file['id'].'\').style.visibility=\'hidden\'" ';
						$preview = '<script type="text/javascript">
								<!--
								document.write("<img style=\"visibility:hidden;width:auto;height:auto;position:absolute;\" id=\"thumb'.$file['id'].'\" src=\"?page=GetAttachmentThumb;file='.$file['id'].'\"  alt=\"'.$file['name'].'\" class=\"image\" />");
								-->
							</script>';
						}
					else
						{
						$hover ='';
						$preview ='';
						}

					$this->addOutput('<tr><td'.$hover.' style="padding:5px;">');
					$this->addCheckbox('files['.$file['id'].']',
					'<a class="link" onclick="return !window.open(this.href);" href="?page=GetAttachment;file='.$file['id'].'">'.$file['name'].'</a>');
					$this->addOutput('</td><td style="text-align:right;padding:5px;vertical-align:bottom;">'.round($file['size'] / 1024, 2).' KByte'.$preview.'</td></tr>');
					}
				$stm->close();

				$this->addOutput('</table><br />');
				}
			catch (DBNoDataException $e)
				{
				$stm->close();
				}

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
		try
			{
			$files = $this->Io->getArray('files');
			}
		catch (IoRequestException $e)
			{
			$files = array();
			}

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
				attachment_id = ?'
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

		$fileID = $this->DB->getInsertId();

		$files[$fileID] = '';

		unlink($this->file['tmp_name']);

		if (strpos($this->file['type'], 'image/jpeg') === 0 ||
			strpos($this->file['type'], 'image/pjpeg') === 0 ||
			strpos($this->file['type'], 'image/png') === 0 ||
			strpos($this->file['type'], 'image/gif') === 0)
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
			$stm->bindInteger($fileID);
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
				closed,
				counter
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
	$this->counter = $data['counter'];

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
// 		if (!$this->Io->isEmpty('name'))
// 			{
			$username = $this->Io->getHtml('name');
// 			}
// 		else
// 			{
// 			$username = 'Gast';
// 			}

		$userid = 0;
		}

 	$this->DB->execute('LOCK TABLES posts WRITE');

	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)
		FROM
			posts
		WHERE
			threadid = ?'
		);
	$stm->bindInteger($this->thread);
	$counter = $stm->getColumn();
	$stm->close();

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
			smilies = ?,
			counter = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($userid);
	$stm->bindString($username);
	$stm->bindString($this->text);
	$stm->bindInteger($this->time);
	$stm->bindInteger($this->smilies ? 1 : 0);

	$stm->bindInteger($counter);

	$stm->execute();
	$stm->close();

	$insertid = $this->DB->getInsertId();

 	$this->DB->execute('UNLOCK TABLES');

	$this->sendFile($insertid);

	$this->updateThread($userid, $username);
	$this->updateForum($userid);
	$this->updateBoard();

	$this->Log->insert($this->thread, $this->time);
	$this->Log->collectGarbage();

	$this->redirect();
	}

protected function updateThread($userid, $username)
	{
 	$this->DB->execute('LOCK TABLES threads WRITE');

	$stm = $this->DB->prepare
		('
		SELECT
			COUNT(*)-1
		FROM
			threads
		WHERE
			forumid = ?
		');
	$stm->bindInteger($this->forum);
	$newCounter = $stm->getColumn();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			counter = counter - 1
		WHERE
			forumid = ?
			AND counter > ?
		');
	$stm->bindInteger($this->forum);
	$stm->bindInteger($this->counter);

	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			counter = ?,
			lastdate = ?,
			lastuserid = ?,
			lastusername = ?,
			posts = posts + 1
		WHERE
			id = ?
		');
	$stm->bindInteger($newCounter);
	$stm->bindInteger($this->time);
	$stm->bindInteger($userid);
	$stm->bindString($username);
	$stm->bindInteger($this->thread);

	$stm->execute();
	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}

protected function updateForum($userid)
	{
 	$stm = $this->DB->prepare
		('
		UPDATE
			forums
		SET
			lastthread = ?,
			lastdate = ?,
			lastposter = ?,
			posts = posts + 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->thread);
	$stm->bindInteger($this->time);
	$stm->bindInteger($userid);
	$stm->bindInteger($this->forum);
	$stm->execute();
	$stm->close();
	}

protected function updateBoard()
	{
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