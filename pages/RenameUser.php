<?php

class RenameUser extends AdminForm{

private $userid = 0;
private $currentname = '';
private $newname = '';

protected function setForm()
	{
	$this->setValue('title', 'Benutzer umbenennen');
	$this->addSubmit('Abschicken');

	if (!$this->User->isLevel(User::ROOT))
		{
		$this->showFailure('kein Zugriff!');
		}

	$this->addText('currentname', 'Aktueller Name', '', 25);	
	$this->requires('currentname');
	$this->setLength('currentname', 3, 25);

	$this->addText('newname', 'Neuer Name', '', 25);
	$this->requires('newname');
	$this->setLength('newname', 3, 25);
	}

protected function checkForm()
	{
	$this->currentname = $this->Io->getString('currentname');
	$this->newname = $this->Io->getString('newname');

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				users
			WHERE
				name = ?
			');
		$stm->bindString(htmlspecialchars($this->currentname));
		$this->userid = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Aktueller Benutzername existiert nicht');
		}

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id
			FROM
				users
			WHERE
				name = ?
			');
		$stm->bindString(htmlspecialchars($this->newname));
		$stm->getColumn();
		$stm->close();

		$this->showWarning('Neuer Benutzername existiert bereits');
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			name = ?
		WHERE
			id = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			posts
		SET
			username = ?
		WHERE
			userid = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			session
		SET
			name = ?
		WHERE
			id = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			lastusername = ?
		WHERE
			lastuserid = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			threads
		SET
			firstusername = ?
		WHERE
			firstuserid = ?
		');
	$stm->bindString(htmlspecialchars($this->newname));
	$stm->bindInteger($this->userid);
	$stm->execute();
	$stm->close();


	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Umbenennung erfolgreich
				</td>
			</tr>
			<tr>
				<td class="main">
					<strong>'.htmlspecialchars($this->currentname).'</strong> heißt jetzt
					<strong>'.htmlspecialchars($this->newname).'</strong>, sonst ändert sich nichts!
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Umbenennung erfolgreich');
	$this->setValue('body', $body);
	}

}

?>