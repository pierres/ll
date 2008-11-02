<?php
/*
	Copyright 2002-2007 Pierre Schmitz <pschmitz@laber-land.de>

	This file is part of LL.

	LL is free software: you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation, either version 3 of the License, or
	(at your option) any later version.

	LL is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with LL.  If not, see <http://www.gnu.org/licenses/>.
*/
class MyProfile extends Form{

private $realname 	= '';
private $gender 	= 0;
private $birthday 	= 0;
private $jabber		= '';
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

	$this->addText('jabber', 'Deine Jabber-Adresse', $this->jabber);
	$this->setLength('jabber', 6, 50);

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
		$this->location = $this->Input->Request->getString('location');
		}
	catch (RequestException $e)
		{
		}

	try
		{
		$this->jabber = $this->Input->Request->getString('jabber');

		if (!$this->Input->Request->isEmpty('jabber'))
			{
			if (!$this->Mail->validateMail($this->jabber))
				{
				$this->showWarning('Keine gültige Jabber-Adresse angegeben!');
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
						jabber = ?
						AND id <> ?'
					);
				$stm->bindString($this->jabber);
				$stm->bindInteger($this->User->getId());
				$stm->getColumn();
				$stm->close();
				$this->showWarning('Jabber-Adresse bereits vergeben!');
				}
			catch (DBNoDataException $e)
				{
				$stm->close();
				}
			}
		}
	catch (RequestException $e)
		{
		}

	try
		{
		$this->plz 	= $this->Input->Request->getInt('plz');

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
	catch (RequestException $e)
		{
		}

	try
		{
		$this->realname = $this->Input->Request->getString('realname');
		}
	catch (RequestException $e)
		{
		}

	try
		{
		$this->gender = $this->Input->Request->getInt('gender');
		}
	catch (RequestException $e)
		{
		}

	try
		{
		$birthday = $this->Input->Request->getString('birthday');

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
	catch (RequestException $e)
		{
		$this->birthday = 0;
		}

	$this->deleteavatar = $this->Input->Request->isValid('deleteavatar');

	try
		{
		$this->avatar = $this->Input->getUploadedFile('avatar');

		if ($this->avatar->getFileSize() >= $this->Settings->getValue('file_size'))
			{
			$this->showWarning('Neuer Avatar ist zu groß!');
			}

		if (strpos($this->avatar->getFileType(), 'image/') !== 0)
			{
			$this->showWarning('Neuer Avatar ist kein Bild!');
			}
		}
	catch (FileException $e)
		{
		}

	try
		{
		$this->text 	= $this->Input->Request->getString('text');
		}
	catch (RequestException $e)
		{
		}

	try
		{
		$this->hiddenStatus = $this->Input->Request->isValid('hidden');
		}
	catch (RequestException $e)
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
				jabber,
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
	$this->jabber 		= unhtmlspecialchars($data['jabber']);
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
			jabber = ?,
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
	$stm->bindString(htmlspecialchars($this->jabber));
	$stm->bindInteger($this->gender);
	$stm->bindInteger($this->birthday);
	$stm->bindString(htmlspecialchars($this->location));
	$stm->bindInteger($this->plz);
	$stm->bindString($text);
	$stm->bindInteger(($this->hiddenStatus ? 1 : 0));
	$stm->bindInteger($this->User->getId());

	$stm->execute();
	$stm->close();

	if (!empty($this->avatar) && !$this->Input->Request->isValid('deleteavatar'))
		{
		$this->sendAvatar();
		}
	elseif($this->hasavatar && $this->Input->Request->isValid('deleteavatar'))
		{
		$this->deleteAvatar();
		}

	$this->Output->redirect('MyProfile');
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
		$content = resizeImage($this->avatar->getFileContent(), $this->avatar->getFileType(), $this->Settings->getValue('avatar_size'));
		}
	catch (Exception $e)
		{
		$content = $this->avatar->getFileContent();
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

	$stm->bindString(htmlspecialchars($this->avatar->getFileName()));
	$stm->bindString($this->avatar->getFileType());
	$stm->bindInteger($this->avatar->getFileSize());
	$stm->bindString($content);
	$stm->bindInteger($this->User->getId());
	$stm->execute();
	$stm->close();

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