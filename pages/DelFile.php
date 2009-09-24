<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU Affero General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Affero General Public License for more details.

	You should have received a copy of the GNU Affero General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/

class DelFile extends Form {

private $file = 0;

protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder');
		}

	try
		{
		$this->file = $this->Input->Get->getInt('file');
		}
	catch (RequestException $e)
		{
		$this->showFailure('Keine Datei angegeben');
		}

	$this->setTitle('Datei löschen');

	$this->setParam('file', $this->file);

	$this->add(new CheckboxInputElement('confirm', 'Bestätigung'));
	$this->add(new SubmitButtonElement($this->getTitle()));
	}

protected function checkForm()
	{
	try
		{
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
		$stm->bindInteger($this->file);
		$stm->bindInteger($this->User->getId());
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Datei nicht gefunden');
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			attachments
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->file);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		DELETE FROM
			attachment_thumbnails
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->file);
	$stm->execute();
	$stm->close();

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				postid
			FROM
				post_attachments
			WHERE
				attachment_id = ?'
			);
		$stm->bindInteger($this->file);

		foreach($stm->getColumnSet() as $post)
			{
			// Das ist also die letzte Datei für diesen Beitrag ...
			$stm2 = $this->DB->prepare
				('
				SELECT
					COUNT(*)
				FROM
					post_attachments
				WHERE
					postid = ?'
				);
			$stm2->bindInteger($post);

			if ($stm2->getColumn() == 1)
				{
				$stm3 = $this->DB->prepare
					('
					UPDATE
						posts
					SET
						file = 0
					WHERE
						id = ?'
					);
				$stm3->bindInteger($post);
				$stm3->execute();
				$stm3->close();
				}
			$stm2->close();
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		}

	$stm = $this->DB->prepare
		('
		DELETE FROM
			post_attachments
		WHERE
			attachment_id = ?'
		);
	$stm->bindInteger($this->file);
	$stm->execute();
	$stm->close();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('MyFiles');
	}


}

?>