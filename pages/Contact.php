<?php


class Contact extends Form{

private $name = '';
private $email = '';
private $sendto = '';


protected function setForm()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				admin_email
			FROM
				boards
			WHERE
				id = ?
			');
		$stm->bindInteger($this->Board->getId());
		$this->sendto = $stm->getColumn();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Keine Ziel-Adresse gefunden!');
		}

	if ($this->User->isOnline())
		{
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					name,
					realname,
					email
				FROM
					users
				WHERE
					id = ?'
				);
			$stm->bindInteger($this->User->getId());
			$data = $stm->getRow();
			$stm->close();

			if (!empty($data['realname']))
				{
				$this->name = $data['realname'];
				}
			else
				{
				$this->name = $data['name'];
				}

			$this->email = $data['email'];
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			}
		}

	$this->setValue('title', 'Kontakt');

	$this->addSubmit('Senden');

	$this->addText('name', 'Dein Name', $this->name, 80);
	$this->requires('name');
	$this->setLength('name', 3, 50);

	$this->addText('email', 'Deine E-Mail-Adresse', $this->email, 80);
	$this->requires('email');
	$this->setLength('email', 6, 50);

	$this->addText('subject', 'Betreff', '', 80);
	$this->requires('subject');
	$this->setLength('subject', 3, 80);

	$this->addTextarea('text', 'Deine Nachricht');
	$this->requires('text');
	$this->setLength('text', 10, 10000);
	}

protected function checkForm()
	{
	$this->name = $this->Io->getString('name');
	$this->email = $this->Io->getString('email');

	if (!$this->Mail->validateMail($this->email))
		{
		$this->showWarning('Keine gültige E-Mail-Adresse angegeben!');
		}

	if (!$this->Mail->validateMail($this->sendto))
		{
		$this->showFailure('Ziel-Adresse '.$this->sendto.' ungültig!');
		}
	}

protected function sendForm()
	{
	$this->Mail->setTo($this->sendto);
	$this->Mail->setReplyTo($this->name.' <'.$this->email.'>');
	$this->Mail->setFrom('support@laber-land.de');
	$this->Mail->setSubject($this->Io->getString('subject'));
	$this->Mail->setText($this->Io->getString('text'));
	$this->Mail->send();

	$body =
		'
		<table class="frame">
			<tr>
				<td class="title">
					E-Mail versandt
				</td>
			</tr>
			<tr>
				<td class="main">
					Deine E-Mail wurde erfolgreich vesandt.
				</td>
			</tr>
		</table>
		';

	$this->setValue('title', 'E-Mail versandt');
	$this->setValue('body', $body);
	}


}

?>