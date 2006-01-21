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
		$files = $this->Sql->fetch
			('
			SELECT
				id,
				name,
				size,
				uploaded
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
		$list .= '<tr>
		<td><a onclick="openLink(this)" class="link" href="?page=GetFile;file='.$file['id'].'">'.$file['name'].'</a></td>
		<td style="text-align:right;">'.round($file['size'] / 1024, 2).'</td>
		<td style="text-align:right;">'.formatDate($file['uploaded']).'</td>
		<td style="text-align:right;"><a href="?page=DelFile;id='.$this->Board->getId().';file='.$file['id'].'"><span class="button" style="background-color:#CC0000">X</span></a></td>
		</tr>';
		}

	try
		{
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
		}
	catch (SqlNoDataException $e)
		{
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

	unlink($this->file['tmp_name']);

	$this->Io->redirect('MyFiles');
	}

}

?>