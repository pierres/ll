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

class MyProfile extends Form {

private $realname 	= '';
private $jabber		= '';
private $deleteavatar	= false;
private $newavatar 	= null;
private $hasavatar	= false;
private $hiddenStatus	= false;


protected function setForm()
	{
	if (!$this->User->isOnline())
		{
		$this->showFailure('Nur für Mitglieder!');
		}

	$this->getData();

	$this->setTitle('Mein Profil');
	$this->add(new SubmitButtonElement('Speichern'));

	$realnameInput = new TextInputElement('realname', $this->realname, 'Dein Name');
	$realnameInput->setMinLength(3);
	$realnameInput->setMaxLength(100);
	$this->add($realnameInput);

	$jabberInput = new TextInputElement('jabber', $this->jabber, 'Deine Jabber-Adresse');
	$jabberInput->setMinLength(6);
	$jabberInput->setMaxLength(50);
	$this->add($jabberInput);

	$hiddenInput = new CheckboxInputElement('hidden', 'unsichtbar');
	$hiddenInput->setChecked($this->hiddenStatus);
	$hiddenInput->setRequired(false);
	$this->add($hiddenInput);

	$avatarInput = new FileInputElement('newavatar', '', 'neuer Avatar');
	$this->add($avatarInput);
	$this->setEncoding('enctype="multipart/form-data"');

	if ($this->hasavatar)
		{
		$this->add(new LabeledElement('Aktueller Avatar', '<img src="'.$this->Output->createUrl('GetAvatar', array('user' => $this->User->getId())).'" alt="" />'));
		$deleteavatarInput = new CheckboxInputElement('deleteavatar', 'Avatar löschen');
		$deleteavatarInput->setChecked($this->deleteavatar);
		$deleteavatarInput->setRequired(false);
		$this->add($deleteavatarInput);
		}

	$this->add(new LabeledElement('Optionen', '
		<ul>
			<li><a href="'.$this->Output->createUrl('ShowUser', array('user' => $this->User->getId())).'"><span>Eigenes Profil ansehen</span></a></li>
			<li><a href="'.$this->Output->createUrl('ChangeEmail').'"><span>E-Mail-Adresse ändern</span></a></li>
			<li><a href="'.$this->Output->createUrl('ChangePassword').'"><span>Passwort ändern</span></a></li>
			<li><a href="'.$this->Output->createUrl('MyFiles').'"><span>Meine Dateien</span></a></li>
			<li><a href="'.$this->Output->createUrl('DeleteUser', array('user' => $this->User->getId())).'"><span>Mein Benutzerkonto löschen</span></a></li>
		</ul>'));
	}

protected function checkForm()
	{
	try
		{
		$this->jabber = $this->Input->Post->getString('jabber');

		if (!$this->Input->Post->isEmptyString('jabber'))
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

	$this->realname = $this->Input->Post->getString('realname', '');
	$this->deleteavatar = $this->Input->Post->isString('deleteavatar');

	try
		{
		$this->newavatar = $this->Input->getUploadedFile('newavatar');

		if ($this->newavatar->getFileSize() >= $this->Settings->getValue('file_size'))
			{
			$this->showWarning('Neuer Avatar ist zu groß!');
			}

		if (strpos($this->newavatar->getFileType(), 'image/') !== 0)
			{
			$this->showWarning('Neuer Avatar ist kein Bild!');
			}
		}
	catch (FileException $e)
		{
		}

	$this->hiddenStatus = $this->Input->Post->isString('hidden');
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
				(SELECT id FROM avatars WHERE id = users.id) AS avatar,
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
	$this->hasavatar 	= !empty($data['avatar']) ;
	$this->hiddenStatus	= ($data['hidden'] == 1);
	}

protected function sendForm()
	{
	$stm = $this->DB->prepare
		('
		UPDATE
			users
		SET
			realname = ?,
			jabber = ?,
			hidden = ?
		WHERE
			id = ?'
		);
	$stm->bindString(htmlspecialchars($this->realname));
	$stm->bindString(htmlspecialchars($this->jabber));
	$stm->bindInteger(($this->hiddenStatus ? 1 : 0));
	$stm->bindInteger($this->User->getId());

	$stm->execute();
	$stm->close();

	if (!empty($this->newavatar) && !$this->deleteavatar)
		{
		$this->sendNewAvatar();
		}
	elseif($this->hasavatar && $this->deleteavatar)
		{
		$this->deleteAvatar();
		}

	$this->Output->redirect('MyProfile');
	}

private function sendNewAvatar()
	{
	try
		{
		$content = resizeImage($this->newavatar->getFileContent(), $this->newavatar->getFileType(), $this->Settings->getValue('avatar_size'));
		}
	catch (Exception $e)
		{
		$content = $this->newavatar->getFileContent();
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

	$stm->bindString(htmlspecialchars($this->newavatar->getFileName()));
	$stm->bindString($this->newavatar->getFileType());
	$stm->bindInteger($this->newavatar->getFileSize());
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