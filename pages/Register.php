<?php

class Register extends Form{

private $email = '';
private $name = '';


protected function setForm()
	{
	$this->setValue('title', 'Registrieren');

	$this->addSubmit('Registrieren');

	$this->addText('name', 'Dein Name', '', 50);
	$this->requires('name');
	$this->setLength('name', 3, 25);
	$this->addElement('namehint', '<span style="font-size:10px;color:red;">Dieser Name wird öffentlich angezeigt.</span>');

	$this->addText('email', 'Deine E-Mail-Adresse', '', 50);
	$this->requires('email');
	$this->setLength('email', 6, 50);

	$this->addElement('emailhint', '<span style="font-size:10px;color:red;">Achte auf die Gültigkeit dieser Adresse,<br /> da die Zugangsdaten dorthin verschickt werden.</span><br /><br />');
	
	$this->addCheckBox('confirmPrivacy', 'Ich bestätige die <a class="link" href="?page=Privacy;id='.$this->Board->getId().'">Datenschutzerklärung</a>');
	$this->requires('confirmPrivacy');
	}

protected function checkForm()
	{
	$this->name = $this->Io->getString('name');
	$this->email = $this->Io->getString('email');

	if (!$this->Mail->validateMail($this->email))
		{
		$this->showWarning('Keine gültige E-Mail-Adresse angegeben!');
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
				OR email = ?
			');
		$stm->bindString(htmlspecialchars($this->name));
		$stm->bindString($this->email);
		$stm->getColumn();
		$stm->close();

		$this->showWarning('Name oder E-Mail bereits vergeben!');
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
		INSERT INTO
			users
		SET
			name = ?,
			email = ?,
			password = ?,
			regdate = ?'
		);
	$stm->bindString(htmlspecialchars($this->name));
	$stm->bindString($this->email);
	$stm->bindString(sha1(generatePassword()));
	$stm->bindInteger(time());
	$stm->execute();
	$stm->close();

	$key = generatePassword();
	$userid = $this->DB->getInsertId();

	$stm = $this->DB->prepare
		('
		DELETE FROM
			password_key
		WHERE
			id = ?'
		);
	$stm->bindInteger($userid);
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		INSERT INTO
			password_key
		SET
			id = ?,
			`key` = ?,
			request_time = ?'
		);
	$stm->bindInteger($userid);
	$stm->bindString($key);
	$stm->bindInteger(time());
	$stm->execute();
	$stm->close();

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom('support@laber-land.de');
	$this->Mail->setSubject('Registrierung bei '.$this->Board->getName());
	$this->Mail->setText(
'Hallo '.$this->name.'!

Deine Registrierung bei "'.$this->Board->getName().'" war erfolgreich.
Du kannst Dein Passwort ändern, wenn Du folgende Seite besuchst:
'.$this->Io->getURL().'?id='.$this->Board->getId().';page=ChangePasswordKey;userid='.$userid.';key='.$key.'

Sollte obiger Link bei Deinem Mail-Programm nicht funktionieren,
so wähle im Anmelde-Dialog die Option "Passwort setzen" und gebe folgende Daten an:
Benutzer-ID:	'.$userid.'
Schlüssel:	'.$key.'



');
	$this->Mail->send();


	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Registrierung erfolgreich
				</td>
			</tr>
			<tr>
				<td class="main">
					Willkommen bei <strong>'.$this->Board->getName().'</strong>, '.htmlspecialchars($this->name).'!
					<p>
					Es wurde ein Aktivierungsschlüssel an <em>'.htmlspecialchars($this->email).'</em> geschickt. Mit diesem kannst Du Dein Passwort einrichten.
					</p>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Registrierung erfolgreich');
	$this->setValue('body', $body);
	}


}


?>