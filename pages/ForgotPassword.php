<?php


class ForgotPassword extends Form{

private $name 	= '';
private $email 	= '';
private $id 	= 0;

protected function setForm()
	{
	$this->setValue('title', 'Passwort vergessen?');

	$this->addSubmit('Erinnern');

	$this->addText('name', 'Dein Name', '', 50);
	$this->requires('name');
	$this->setLength('name', 3, 25);

	$this->addText('email', 'Deine E-Mail-Adresse', '',  50);
	$this->requires('email');
	$this->setLength('email', 5, 50);
	}

protected function checkForm()
	{
	/** TODO: Hier müssen noch einige Maßnahmen gegen Mißbrauch ergriffen werden */

	$this->name = $this->Io->getHtml('name');
	$this->email = $this->Io->getString('email');

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
				AND email = ?
			');
		$stm->bindString($this->name);
		$stm->bindString($this->email);
		$this->id = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showWarning('Name und E-Mail wurden nicht gefunden.');
		}
	}

protected function sendForm()
	{
	$key = md5(generatePassword());

	$stm = $this->DB->prepare
		('
		DELETE FROM
			change_password
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->id);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			change_password
		SET
			id = ?,
			`key` = ?,
			request_time = ?'
		);
	$stm->bindInteger($this->id);
	$stm->bindString($key);
	$stm->bindInteger(time());
	$stm->execute();
	$stm->close();

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom('support@laber-land.de');
	$this->Mail->setSubject('Dein Passwort im Laber-Land');
	$this->Mail->setText(
'Hallo '.$this->name.'!

Du kannst Dein Passwort ändern, wenn Du folgende Seite besuchst:
'.$this->Io->getURL().'?id='.$this->Board->getId().';page=ChangePasswordKey;userid='.$this->id.';key='.$key.'

Solltest Du Dir diese Erinnerung nicht geschickt haben, so kannst Du diese Nachricht ignorieren.
Dein altes Passwort bleibt dann weiterhin gültig.');
	$this->Mail->send();

	$this->Io->redirect('Login');
	}
}

?>