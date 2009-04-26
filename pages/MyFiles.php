<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class MyFiles extends Form {

private $file = null;

protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->setTitle('Meine Dateien');

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

	$list =
		'<table>
		<tr>
		<th>Datei</th>
		<th style="width:120px;">Größe&nbsp;(KByte)</th>
		<th style="width:200px;">Typ</th>
		<th>Datum</th>
		<th style="width:80px;"></th>
		</tr>';

	foreach ($files as $file)
		{
		$list .= '<tr>
		<td><a href="'.$this->Output->createUrl('GetAttachment', array('file' => $file['id'])).'">'.$file['name'].'</a></td>
		<td style="text-align:right;">'.round($file['size'] / 1024, 2).'</td>
		<td style="text-align:right;">'.$file['type'].'</td>
		<td style="text-align:right;">'.$this->L10n->getDateTime($file['uploaded']).'</td>
		<td style="text-align:right;"><a href="'.$this->Output->createUrl('DelFile', array('file' => $file['id'])).'">löschen</a></td>
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
		<th style="padding-top:10px;">Noch '.($this->Settings->getValue('files') - $data['files']).' Dateien übrig</th>
		<th style="text-align:right;padding-top:10px;">Noch '.round(($this->Settings->getValue('quota') - $data['quota']) / 1024, 2).'</th>
		<th></th>
		<th></th>
		<th></th>
		</tr></table>';

	$this->add(new PassiveFormElement($list));
	$this->add(new DividerElement());
	$this->add(new SubmitButtonElement('Hochladen'));
	$this->add(new FileInputElement('file', '', 'Datei hinzufügen'));
	$this->setEncoding('enctype="multipart/form-data"');
	}

protected function checkForm()
	{
	try
		{
		$this->file = $this->Input->getUploadedFile('file');
		}
	catch (FileException $e)
		{
		$this->showWarning($e->getMessage());
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

	if ($data['quota'] + $this->file->getFileSize() >=  $this->Settings->getValue('quota'))
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
	$stm = $this->DB->prepare
		('
		INSERT INTO
			attachments
		SET
			name = ?,
			type = ?,
			content = ?,
			size = ?,
			userid = ?,
			uploaded = ?'
		);
	$stm->bindString(htmlspecialchars($this->file->getFileName()));
	$stm->bindString($this->file->getFileType());
	$stm->bindString($this->file->getFileContent());
	$stm->bindInteger($this->file->getFileSize());
	$stm->bindInteger($this->User->getId());
	$stm->bindInteger($this->Input->getTime());
	$stm->execute();
	$stm->close();

	if (strpos($this->file->getFileType(), 'image/jpeg') === 0 ||
			strpos($this->file->getFileType(), 'image/pjpeg') === 0 ||
			strpos($this->file->getFileType(), 'image/png') === 0 ||
			strpos($this->file->getFileType(), 'image/gif') === 0)
			{
			try
				{
				$thumbcontent = resizeImage($this->file->getFileContent(), $this->file->getFileType(), $this->Settings->getValue('thumb_size'));
				}
			catch (Exception $e)
				{
				$this->Output->redirect('MyFiles');
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

	$this->Output->redirect('MyFiles');
	}

}

?>