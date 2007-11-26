<?php


class MyProfile extends Form{

private $realname 	= '';
private $gender 	= 0;
private $birthday 	= 0;
private $email 		= '';
private $location 	= '';
private $plz		= '';
private $deleteavatar	= false;
private $avatar 	= array();
private $hasavatar	= false;
private $text		= '';
private $hiddenStatus	= false;


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

	$this->addText('plz', 'Deine Postleitzahl', !empty($this->plz) ? $this->plz : '', 5);
	$this->setLength('plz', 5, 5);

	$this->addCheckBox('hidden', 'Online-Status verstecken', $this->hiddenStatus);

	$this->showAvatar();

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
			$stm->close();

			if (empty($this->location))
				{
				$this->location = $location;
				}
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
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

	$this->deleteavatar = $this->Io->isRequest('deleteavatar');

	try
		{
		$this->avatar = $this->Io->getUploadedFile('avatar');

		if ($this->avatar['size'] >= $this->Settings->getValue('file_size'))
			{
			$this->showWarning('Neuer Avatar ist zu groß!');
			}

		if (strpos($this->avatar['type'], 'image/') !== 0)
			{
			$this->showWarning('Neuer Avatar ist kein Bild!');
			}
		}
	catch (IoException $e)
		{
		}

	try
		{
		$this->text 	= $this->Io->getString('text');
		}
	catch (IoRequestException $e)
		{
		}

	try
		{
		$this->hiddenStatus = $this->Io->isRequest('hidden');
		}
	catch (IoRequestException $e)
		{
		}
	}

private function getData()
	{
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				realname,
				gender,
				birthday,
				location,
				plz,
				(SELECT id FROM avatars WHERE id = users.id) AS avatar,
				text,
				hidden
			FROM
				users
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->User->getId());
		$data = $stm->getRow();
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		$this->showFailure('Es wurde kein Benutzerkonto gefunden!');
		}

	$this->realname 	= unhtmlspecialchars($data['realname']);
	$this->gender		= $data['gender'];
	$this->birthday		= $data['birthday'];
	$this->location 	= unhtmlspecialchars($data['location']);
	$this->plz 		= $data['plz'];
 	$this->hasavatar 	= !empty($data['avatar']) ;
	$this->text 		= $this->UnMarkup->fromHtml($data['text']);
	$this->hiddenStatus	= ($data['hidden'] == 1);
	}

protected function sendForm()
	{
	$text = $this->Markup->toHtml($this->text);
	// BugFix for Bug#1
	if ($length = strlen($text) > 65536)
		{
		$this->showWarning('Der Text ist '.($length-65536).' Zeichen zu lang!');
		$this->showForm();
		return;
		}

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
			text = ?,
			hidden = ?
		WHERE
			id = ?'
		);
	$stm->bindString(htmlspecialchars($this->realname));
	$stm->bindInteger($this->gender);
	$stm->bindInteger($this->birthday);
	$stm->bindString(htmlspecialchars($this->location));
	$stm->bindInteger($this->plz);
	$stm->bindString($text);
	$stm->bindInteger(($this->hiddenStatus ? 1 : 0));
	$stm->bindInteger($this->User->getId());

	$stm->execute();
	$stm->close();

	if (!empty($this->avatar) && !$this->Io->isRequest('deleteavatar'))
		{
		$this->sendAvatar();
		}
	elseif($this->hasavatar && $this->Io->isRequest('deleteavatar'))
		{
		$this->deleteAvatar();
		}

	$this->Io->redirect('MyProfile');
	}

private function showAvatar()
	{
	$this->addOutput('<fieldset><legend>Avatar</legend>');
	if ($this->hasavatar)
		{
		$this->addOutput('<img src="?page=GetAvatar;user='.$this->User->getId().'" class="avatar" alt="" />');
		$this->addCheckbox('deleteavatar', 'Avatar löschen');
		$this->addOutput('<br />');
		}
	$this->addFile('avatar', 'neuer Avatar', 25);
	$this->addOutput('</fieldset>');
	}

private function sendAvatar()
	{
	try
		{
		$content = resizeImage(file_get_contents($this->avatar['tmp_name']), $this->avatar['type'], $this->Settings->getValue('avatar_size'));
		}
	catch (Exception $e)
		{
		$content = file_get_contents($this->avatar['tmp_name']);
		}

	if ($this->hasavatar)
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				avatars
			SET
				name = ?,
				type = ?,
				size = ?,
				content = ?
			WHERE
				id = ?'
			);
		}
	else
		{
		$stm = $this->DB->prepare
			('
			INSERT INTO
				avatars
			SET
				name = ?,
				type = ?,
				size = ?,
				content = ?,
				id = ?'
			);
		}

	$stm->bindString(htmlspecialchars($this->avatar['name']));
	$stm->bindString($this->avatar['type']);
	$stm->bindInteger(strlen($content));
	$stm->bindString($content);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	unlink($this->avatar['tmp_name']);

	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			avatar = 1
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();
	}

private function deleteAvatar()
	{
	$stm = $this->DB->prepare
		('
		DELETE FROM
			avatars
		WHERE
			id = ?'
		);

	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			avatar = 0
		WHERE
			id = ?'
		);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();
	}

}

?>