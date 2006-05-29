<?php

class ChangeEmail extends Form{

private $email;

protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->setValue('title', 'E-Mail-Adresse ändern');

	$this->addSubmit('Ändern');

	$this->addText('email', 'Deine E-Mail-Adresse', '', 50);
	$this->requires('email');
	$this->setLength('email', 6, 50);

	$this->addText('confirm', 'Bestätige Deine E-Mail-Adresse', '', 50);
	$this->requires('confirm');
	$this->setLength('confirm', 6, 50);

	$this->addElement('hint', 'Achte auf die Gültigkeit dieser Adresse,<br /> da die Zugangsdaten dorthin verschickt werden.');
	}

protected function checkForm()
	{
	$this->email = $this->Io->getString('email');

	if (!$this->Mail->validateMail($this->email))
		{
		$this->showWarning('Keine gültige E-Mail-Adresse angegeben!');
		}

	if ($this->email != $this->Io->getString('confirm'))
		{
		$this->showWarning('Du hast Dich vertippt!');
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
				email = ?'
			);
		$stm->bindString($this->email);
		$stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		return;
		}

	$this->showWarning('E-Mail-Adresse bereits vergeben!');
	}

protected function sendForm()
	{
	$password = generatePassword();

	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			email = ?,
			password = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->email);
	$stm->bindString(md5($password));
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	$this->Mail->setTo($this->email);
	$this->Mail->setFrom('support@laber-land.de');
	$this->Mail->setSubject('Dein Passwort im Laber-Land');
	$this->Mail->setText(
<<<eot
Hallo!

Dein Passwort lautet: {$password}

Viel Spass in der Forengemeinschaft wuenscht Dir das LL-Team.
eot
);
	$this->Mail->send();

	$this->User->logout();

	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					Neues Passwort erstellt
				</td>
			</tr>
			<tr>
				<td class="main">
					Hallo!
					<p>
					Dein neues Passwort wurde Dir soeben an <em>'.htmlspecialchars($this->email).'</em> geschickt. Mit diesem kannst Du Dich nun <a href="?page=Login;id='.$this->Board->getId().'" class="button">Anmelden</a>.
					</p>
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'Neues Passwort erstellt');
	$this->setValue('body', $body);
	}


}


?>