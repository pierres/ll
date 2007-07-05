<?php


class MyFiles extends Form{

private $file = array();

protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->setValue('title', 'Meine Dateien');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name,
				size,
				uploaded,
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
		}
	catch (DBNoDataException $e)
		{
		$files = array();
		}

	$this->addOutput('<script type="text/javascript">
			/* <![CDATA[ */
			function writeElement(text)
				{
				var div = document.createElementNS("http://www.w3.org/1999/xhtml","div");
				div.innerHTML = text;
				var pos;
				pos = document;
				while(pos.lastChild && pos.lastChild.nodeType==1)
					pos = pos.lastChild;
				var nodes = div.childNodes;
				while(nodes.length)
					pos.parentNode.appendChild(nodes[0]);
				}
			/* ]]> */
		</script>');

	$list =
		'<table style="margin:10px;width:600px;">
		<tr>
		<td style="padding-bottom:5px;"><strong>Datei</strong></td>
		<td style="text-align:right;padding-bottom:5px;"><strong>Größe</strong>&nbsp;(KByte)</td>
		<td style="text-align:right;padding-bottom:5px;"><strong>Datum</strong></td>
		<td></td>
		</tr>';

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
						/* <![CDATA[ */
						writeElement("<img style=\"visibility:hidden;width:auto;height:auto;position:absolute;z-index:10;\" id=\"thumb'.$file['id'].'\" src=\"?page=GetAttachmentThumb;file='.$file['id'].'\"  alt=\"'.$file['name'].'\" class=\"image\" />");
						/* ]]> */
					</script>';
			}
		else
			{
			$hover ='';
			$preview ='';
			}

		$list .= '<tr>
		<td'.$hover.'><a  onclick="return !window.open(this.href);" class="link" href="?page=GetAttachment;file='.$file['id'].'">'.$file['name'].'</a></td>
		<td style="text-align:right;">'.$preview.round($file['size'] / 1024, 2).'</td>
		<td style="text-align:right;">'.formatDate($file['uploaded']).'</td>
		<td style="text-align:right;"><a href="?page=DelFile;id='.$this->Board->getId().';file='.$file['id'].'"><span class="button" style="background-color:#CC0000">X</span></a></td>
		</tr>';
		}
	$stm->close();
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
		$data['files'] = 0;
		$data['quota'] = 0;
		}

	$list .= '<tr>
		<td style="padding-top:10px;"><strong>Noch '.($this->Settings->getValue('files') - $data['files']).' Dateien übrig</strong></td>
		<td style="text-align:right;padding-top:10px;"><strong>Noch '.round(($this->Settings->getValue('quota') - $data['quota']) / 1024, 2).'</strong></td>
		<td></td>
		<td></td>
		</tr></table>';

	$this->addOutput($list);

	$this->addSubmit('Hochladen');

	$this->addFile('file', 'Datei hinzufügen');
	}

protected function checkForm()
	{
	try
		{
		$this->file = $this->Io->getUploadedFile('file');
		}
	catch (IoException $e)
		{
		$this->showWarning($e->getMessage());
		return;
		}

	if ($this->file['size'] >= $this->Settings->getValue('file_size'))
		{
		$this->showWarning('Datei ist zu groß!');
		return;
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
		$data['files'] = 0;
		$data['quota'] = 0;
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

protected function sendForm()
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
				$this->Io->redirect('MyFiles');
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
			$stm->close();
			}

	$this->Io->redirect('MyFiles');
	}

}

?>