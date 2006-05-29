<?php


class AdminSettings extends AdminForm{


private $admin = 0;
private $admins = array();
private $mods = array();

protected function setForm()
	{
	$this->setValue('title', 'Einstellungen');

	$this->addSubmit('Speichern');

	$this->addText('name', 'Name', $this->Board->getName());
	$this->requires('name');
	$this->setLength('name', 3, 100);

	if($this->User->isLevel(User::ADMIN))
		{
		$this->addText('admin', 'Administrator', AdminFunctions::getUserName($this->Board->getAdmin()));
		$this->requires('admin');
		}

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
		$this->addTextArea('admins', 'Administratoren', $admins, 80, 5);
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
	$this->addTextArea('mods', 'Moderatoren', $mods, 80, 5);

	try
		{
		$stm = $this->DB->prepare
			('
			SELECT
				description
			FROM
				boards
			WHERE id = ?'
			);
		$stm->bindInteger($this->Board->getId());
		$description = $stm->getColumn();
		}
	catch (DBNoDataException $e)
		{
		$description = '';
		}
	$stm->close();

	$this->addTextArea('description', 'Beschreibung', $this->UnMarkup->fromHtml($description));
	}

protected function checkForm()
	{
	if($this->User->isLevel(User::ADMIN))
		{
		try
			{
			$this->admin = AdminFunctions::getUserId($this->Io->getString('admin'));
			}
		catch (DBNoDataException $e)
			{
			$this->showWarning('Administrator nicht gefunden');
			}
		}

	if(!$this->Io->isEmpty('admins') && ($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN)))
		{
		$admins = array_map('trim', explode("\n", $this->Io->getString('admins')));

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

	if(!$this->Io->isEmpty('mods'))
		{
		$mods = array_map('trim', explode("\n", $this->Io->getString('mods')));

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
	}

protected function sendForm()
	{
	$description = $this->Markup->toHtml($this->Io->getString('description'));
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

	$stm = $this->DB->prepare
		('
		UPDATE
			boards
		SET
			name = ?,
			description = ?
		WHERE
			id = ?'
		);
	$stm->bindString($this->Io->getHtml('name'));
	$stm->bindString($description);
	$stm->bindInteger($this->Board->getId());
	$stm->execute();
	$stm->close();

	$this->updateAdmins();
	$this->updateMods();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminSettings');
	}

private function updateMods()
	{
	if ($this->Board->getMods() == 0 && !empty($this->mods))
		{
		/** FIXME:
			Gleichzeitiger Zugriff könnte Überscheindung zur Folge haben -> Tabellen sperren
			Das hilft so aber auch nicht unbedingt viel :-(
		*/
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
		/** FIXME: Gleichzeitiger Zugriff könnte Überscheindung zur Folge haben -> Tabellen sperren */
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