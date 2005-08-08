<?php


class Contact extends Form{

private $name = '';
private $email = '';



protected function setForm()
	{
	if ($this->User->isOnline())
		{
		try
			{
			$data = $this->Sql->fetchRow
				('
				SELECT
					name,
					realname,
					email
				FROM
					users
				WHERE
					id = '.$this->User->getId()
				);

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
		catch (SqlNoDataException $e)
			{
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
	}

protected function sendForm()
	{
	$this->Mail->setTo('support@laber-land.de');
	$this->Mail->setFrom($this->name.' <'.$this->email.'>');
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