<?php

class DelFile extends Form {

private $file = 0;

protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	try
		{
		$this->file = $this->Io->getInt('file');
		}
	catch (IoRequestException $e)
		{
		$this->showFailure('Keine Datei angegeben!');
		}

	$this->setValue('title', 'Datei löschen');

	$this->addHidden('file', $this->file);
	$this->requires('file');

	$this->addOutput('Soll die Datei wirklich gelöscht werden?');

	$this->addSubmit('Datei löschen');
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
		$this->showFailure('Datei nicht gefunden!');
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
	$this->Io->redirect('MyFiles');
	}


}

?>