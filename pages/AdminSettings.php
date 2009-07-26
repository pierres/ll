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

class AdminSettings extends AdminForm {


private $admin = 0;
private $admins = array();
private $mods = array();

protected function setForm()
	{
	$this->setTitle('Einstellungen');

	$this->add(new SubmitButtonElement('Speichern'));

	$nameInput = new TextInputElement('name', $this->Board->getName(), 'Name');
	$nameInput->setMinLength(3);
	$nameInput->setMaxLength(100);
	$this->add($nameInput);

	if($this->User->isLevel(User::ADMIN))
		{
		$this->add(new TextInputElement('admin', AdminFunctions::getUserName($this->Board->getAdmin()), 'Administrator'));
		}

	$mods = '';
	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				users.name
			FROM
				users,
				user_group
			WHERE
				user_group.userid = users.id
				AND user_group.groupid = ?'
			);
		$stm->bindInteger($this->Board->getMods());

		foreach($stm->getColumnSet() as $mod)
			{
			$mods .= $mod."\n";
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}
	$modsInput = new TextareaInputElement('mods', $mods, 'Moderatoren');
	$modsInput->setRows(5);
	$this->add($modsInput);

	if($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN))
		{
		$admins = '';
		try
			{
			$stm = $this->DB->prepare
				('
				SELECT
					users.name
				FROM
					users,
					user_group
				WHERE
					user_group.userid = users.id
					AND user_group.groupid = ?'
				);
			$stm->bindInteger($this->Board->getAdmins());

			foreach($stm->getColumnSet() as $admin)
				{
				$admins .= $admin."\n";
				}
			$stm->close();
			}
		catch (DBNoDataException $e)
			{
			$stm->close();
			}
		$adminsInput = new TextareaInputElement('admins', $admins, 'Administratoren');
		$adminsInput->setRows(5);
		$this->add($adminsInput);

		$hostInput = new TextInputElement('host', $this->Board->getHost(), 'Host/Domain');
		$hostInput->setMinLength(6);
		$hostInput->setMaxLength(100);
		$this->add($hostInput);

		$stm = $this->DB->prepare
			('
			SELECT
				admin_name,
				admin_email,
				admin_tel,
				admin_address
			FROM
				boards
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->Board->getId());
		$address = $stm->getRow();

		$this->add(new TextInputElement('admin_name', $address['admin_name'], 'Name'));
		$this->add(new TextInputElement('admin_email', $address['admin_email'], 'E-Mail'));
		$this->add(new TextInputElement('admin_tel', $address['admin_tel'], 'Telefon'));
		$addressInput = new TextareaInputElement('admin_address', br2nl($address['admin_address']), 'Adresse');
		$addressInput->setRows(5);
		$this->add($addressInput);
		}
	}

protected function checkForm()
	{
	if($this->User->isLevel(User::ADMIN))
		{
		try
			{
			$this->admin = AdminFunctions::getUserId($this->Input->Post->getString('admin'));
			}
		catch (DBNoDataException $e)
			{
			$this->showWarning('Administrator nicht gefunden');
			}
		}

	if(!$this->Input->Post->isEmptyString('admins') && ($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN)))
		{
		$admins = array_map('trim', explode("\n", $this->Input->Post->getString('admins')));

		foreach ($admins as $admin)
			{
			try
				{
				$this->admins[] = AdminFunctions::getUserId($admin);
				}
			catch (DBNoDataException $e)
				{
				$this->showWarning('Administrator "'.htmlspecialchars($admin).'" nicht gefunden');
				}
			}
		}
	if(!$this->Input->Post->isEmptyString('host') && ($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN)))
		{
		try
			{
			$fp = fsockopen($this->Input->Post->getString('host'), 80, $errno, $errstr, 5);
			if (!$fp)
				{
				$this->showWarning('Fehler beim Verbinden mit Host <em>'.$this->Input->Post->getString('host').'</em>: <strong>'.$errstr.'</strong>.');
				}
			else
				{
				fclose($fp);
				}
			if (gethostbyname($this->Input->Post->getString('host')) != getenv('SERVER_ADDR'))
				{
				$this->showWarning('Der Host <em>'.$this->Input->Post->getString('host').'</em> zeigt nicht auf die IP <em>'.getenv('SERVER_ADDR').'</em>.');
				}
			}
		catch (InternalRuntimeException $e)
			{
			$this->showWarning('Fehler beim Verbinden mit Host <em>'.$this->Input->Post->getString('host').'</em>:<div style="color:darkred;margin-left:50px;">'.$e->getMessage().'</div>');
			}
		}

	if(!$this->Input->Post->isEmptyString('mods'))
		{
		$mods = array_map('trim', explode("\n", $this->Input->Post->getString('mods')));

		foreach ($mods as $mod)
			{
			try
				{
				$this->mods[] = AdminFunctions::getUserId($mod);
				}
			catch (DBNoDataException $e)
				{
				$this->showWarning('Moderator "'.htmlspecialchars($mod).'" nicht gefunden');
				}
			}
		}

	if($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN))
		{
		if (!$this->Mail->validateMail($this->Input->Post->getHtml('admin_email')))
			{
			$this->showWarning('Keine gültige E-Mail-Adresse angegeben!');
			}
		}
	}

protected function sendForm()
	{
	$description = $this->Markup->toHtml($this->Input->Post->getString('description'));
	// BugFix for Bug#1
	if ($length = strlen($description) > 65536)
		{
		$this->showWarning('Der Text ist '.($length-65536).' Zeichen zu lang!');
		$this->showForm();
		return;
		}

	if($this->User->isLevel(User::ADMIN))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				boards
			SET
				admin = ?
			WHERE
				id = ?'
			);
		$stm->bindInteger($this->admin);
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}

	if(!$this->Input->Post->isEmptyString('host') && ($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN)))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				boards
			SET
				host = ?
			WHERE
				id = ?'
			);
		$stm->bindString($this->Input->Post->getString('host'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}

	if($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN))
		{
		$stm = $this->DB->prepare
			('
			UPDATE
				boards
			SET
				admin_name = ?,
				admin_address = ?,
				admin_email = ?,
				admin_tel = ?
			WHERE
				id = ?'
			);
		$stm->bindString($this->Input->Post->getHtml('admin_name'));
		$stm->bindString(nl2br($this->Input->Post->getHtml('admin_address')));
		$stm->bindString($this->Input->Post->getHtml('admin_email'));
		$stm->bindString($this->Input->Post->getHtml('admin_tel'));
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}


	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			name = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->Input->Post->getHtml('name'));
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	if($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN))
		{
		$this->updateAdmins();
		}

	$this->updateMods();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Output->redirect('AdminSettings');
	}

private function updateMods()
	{
	if ($this->Board->getMods() == 0 && !empty($this->mods))
		{
		$this->DB->execute('LOCK TABLES user_group WRITE, boards WRITE');
		$groupid = $this->DB->getColumn
			('
			SELECT
				MAX(groupid)
			FROM
				user_group
			') + 1;

		$stm = $this->DB->prepare
			('
			UPDATE
				boards
			SET
				mods = ?
			WHERE
				id = ?'
			);
		$stm->bindInteger($groupid);
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}
	else
		{
		$groupid = $this->Board->getMods();

		$stm = $this->DB->prepare
			('
			DELETE FROM
				user_group
			WHERE
				groupid = ?'
			);
		$stm->bindInteger($groupid);
		$stm->execute();
		$stm->close();
		}

	$stm = $this->DB->prepare
		('
		INSERT INTO
			user_group
		SET
			groupid = ?,
			userid = ?'
		);
	foreach($this->mods as $mod)
		{
		$stm->bindInteger($groupid);
		$stm->bindInteger($mod);
		$stm->execute();
		}
	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}

private function updateAdmins()
	{
	if ($this->Board->getAdmins() == 0 && !empty($this->admins))
		{
		$this->DB->execute('LOCK TABLES user_group WRITE, boards WRITE');
		$groupid = $this->DB->getColumn
			('
			SELECT
				MAX(groupid)
			FROM
				user_group
			') + 1;
		$stm = $this->DB->prepare
			('
			UPDATE
				boards
			SET
				admins = ?
			WHERE
				id = ?'
			);
		$stm->bindInteger($groupid);
		$stm->bindInteger($this->Board->getId());
		$stm->execute();
		$stm->close();
		}
	else
		{
		$groupid = $this->Board->getAdmins();

		$stm = $this->DB->prepare
			('
			DELETE FROM
				user_group
			WHERE
				groupid = ?'
			);
		$stm->bindInteger($groupid);
		$stm->execute();
		$stm->close();
		}

	$stm = $this->DB->prepare
		('
		INSERT INTO
			user_group
		SET
			groupid = ?,
			userid = ?'
		);
	foreach($this->admins as $admin)
		{
		$stm->bindInteger($groupid);
		$stm->bindInteger($admin);
		$stm->execute();
		}
	$stm->close();

	$this->DB->execute('UNLOCK TABLES');
	}

}


?>