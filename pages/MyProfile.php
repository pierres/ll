<?php


class MyProfile extends Form{

private $realname 	= '';
private $gender 	= 0;
private $birthday 	= 0;
private $email 		= '';
private $location 	= '';
private $plz		= '';
private $avatar		= '';
private $text		= '';


protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->getData();

	$this->setValue('title', 'Mein Profil');
	$this->addSubmit('Speichern');

	$this->addText('realname', 'Dein Name', $this->realname);
	$this->setLength('realname', 3, 100);

	$gender = array('männlich' => 1, 'weiblich' => 2, 'weiß nicht' => 0);
	$this->addRadio('gender', 'Geschlecht', $gender, $this->gender);
	$this->setLength('gender', 1, 1);

	$this->addText('birthday', 'Dein Geburtstag', (!empty($this->birthday) ? date('d.m.Y', $this->birthday) : ''), 10);
	$this->setLength('birthday', 6, 10);

	$this->addText('location', 'Dein Wohnort', $this->location);
	$this->setLength('location', 3, 255);
	/** FIXME */
	$this->addText('plz', 'Deine Postleitzahl', !empty($this->plz) ? $this->plz : '', 5);
	$this->setLength('plz', 5, 5);

	$this->listMyFiles();

	$this->addTextarea('text', 'Freier Text', $this->text);
	$this->setLength('text', 3, 65536);

	$this->addElement('buttons', '<a href="?page=ShowUser;id='.$this->Board->getId().';user='.$this->User->getId().'"><span class="button">Eigenes Profil ansehen</span></a> <a href="?page=ChangeEmail;id='.$this->Board->getId().'"><span class="button">E-Mail-Adresse ändern</span></a> <a href="?page=ChangePassword;id='.$this->Board->getId().'"><span class="button">Passwort ändern</span></a> <a href="?page=MyFiles;id='.$this->Board->getId().'"><span class="button">Meine Dateien</span></a> <a href="?page=DeleteUser;id='.$this->Board->getId().';user='.$this->User->getId().'"><span class="button">Mein Benutzerkonto löschen</span></a>');
	}

protected function checkForm()
	{
	try
		{
		$this->location = $this->Io->getString('location');
		}
	catch (IoRequestException $e)
		{
		}

	try
		{
		$this->plz 	= $this->Io->getInt('plz');

		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					location
				FROM
					plz
				WHERE
					code = ?'
				);
			$stm->bindInteger($this->plz);
			$location = $stm->getColumn();

			if (empty($this->location))
				{
				$this->location = $location;
				}
			}
		catch (DBNoDataException $e)
			{
			/** FIXME */
			if (!empty($this->plz))
				{
				$this->showWarning('Postleitzahl nicht gefunden');
				}
			}
		}
	catch (IoRequestException $e)
		{
		}

	try
		{
		$this->realname = $this->Io->getString('realname');
		}
	catch (IoRequestException $e)
		{
		}

	try
		{
		$this->gender = $this->Io->getInt('gender');
		}
	catch (IoRequestException $e)
		{
		}

	try
		{
		$birthday = $this->Io->getString('birthday');

		if (!empty($birthday))
			{
			$birthday = strtotime(preg_replace('/(\d+)\.(\d+)\.(\d+)/', '$3-$2-$1', $birthday));

			if (!$birthday || empty($birthday))
				{
				$this->showWarning('Konnte Deinen Geburtstag nicht bestimmen');
				}
			else
				{
				$this->birthday = $birthday;
				}
			}
		else
			{
			$this->birthday = 0;
			}
		}
	catch (IoRequestException $e)
		{
		$this->birthday = 0;
		}

	try
		{
		$this->avatar = $this->Io->getInt('avatar');
		}
	catch (IoRequestException $e)
		{
		$this->avatar = 0;
		}

	try
		{
		$this->text 	= $this->Io->getString('text');
		}
	catch (IoRequestException $e)
		{
		}
	}

private function getData()
	{
	$stm = $this->DB->prepare
		('
		SELECT
			realname,
			gender,
			birthday,
			location,
			plz,
			avatar,
			text
		FROM
			users
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->User->getId());
	$data = $stm->getRow();

	$this->realname 	= unhtmlspecialchars($data['realname']);
	$this->gender		= $data['gender'];
	$this->birthday		= $data['birthday'];
	$this->location 	= unhtmlspecialchars($data['location']);
	$this->plz 		= $data['plz'];
	$this->avatar 		= $data['avatar'];
	$this->text 		= $this->UnMarkup->fromHtml($data['text']);
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			realname = ?,
			gender = ?,
			birthday = ?,
			location = ?,
			plz = ?,
			avatar = ?,
			text = ?
		WHERE
			id = ?'
		);
	$stm->bindString(htmlspecialchars($this->realname));
	$stm->bindInteger($this->gender);
	$stm->bindInteger($this->birthday);
	$stm->bindString(htmlspecialchars($this->location));
	$stm->bindInteger($this->plz);
	$stm->bindInteger($this->avatar);
	$stm->bindString($this->Markup->toHtml($this->text));
	$stm->bindInteger($this->User->getId());

	$stm->execute();

	$this->Io->redirect('MyProfile');
	}

private function listMyFiles()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				id,
				name
			FROM
				files
			WHERE
				userid = ?
				AND type LIKE \'image/%\'
				AND size <= ?
			ORDER BY
				id DESC
			');
		$stm->bindInteger($this->User->getId());
		$stm->bindInteger($this->Settings->getValue('avatar_size'));
		$files = $stm->getRowSet();
		}
	catch (DBNoDataException $e)
		{
		$files = array();
		}

	$list = array('<em>kein Avatar</em>' => 0);

	foreach ($files as $file)
		{
		$list['<a class="link" onclick="openLink(this)" href="?page=GetFile;file='.$file['id'].'">'.$file['name'].'</a>'] = $file['id'];
		}

	$this->addRadio('avatar', 'Avatar', $list, $this->avatar);
	}

}

?>