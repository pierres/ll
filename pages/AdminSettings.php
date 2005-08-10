<?php


class AdminSettings extends AdminForm{


private $admin = 0;
private $admins = array();
private $mods = array();

protected function setForm()
	{
	$this->setValue('title', 'Einstellungen');

	$this->addSubmit('Speichern');

	$this->addText('name', 'Name', htmlspecialchars($this->Board->getName()));
	$this->requires('name');
	$this->setLength('name', 3, 100);

	if($this->User->isLevel(User::ADMIN))
		{
		$this->addText('admin', 'Administrator', htmlspecialchars(AdminFunctions::getUserName($this->Board->getAdmin())));
		$this->requires('admin');
		}

	if($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN))
		{
		try
			{
			$admins = $this->Sql->fetchCol
				('
				SELECT
					users.name
				FROM
					users,
					user_group
				WHERE
					user_group.userid = users.id
					AND user_group.groupid = '.$this->Board->getAdmins()
				);

			$this->addTextArea('admins', 'Administratoren', implode("\n", $admins), 80, 5);
			}
		catch (SqlNoDataException $e)
			{
			$this->addTextArea('admins', 'Administratoren', '', 80, 5);
			}
		}

	try
		{
		$admins = $this->Sql->fetchCol
			('
			SELECT
				users.name
			FROM
				users,
				user_group
			WHERE
				user_group.userid = users.id
				AND user_group.groupid = '.$this->Board->getMods()
			);

		$this->addTextArea('mods', 'Moderatoren', implode("\n", $admins), 80, 5);
		}
	catch (SqlNoDataException $e)
		{
		$this->addTextArea('mods', 'Moderatoren', '', 80, 5);
		}

	$this->addTextArea('description', 'Beschreibung', $this->UnMarkup->fromHtml($this->Sql->fetchValue('SELECT description FROM boards WHERE id = '.$this->Board->getId())));
	}

protected function checkForm()
	{
	if($this->User->isLevel(User::ADMIN))
		{
		try
			{
			$this->admin = AdminFunctions::getUserId($this->Io->getString('admin'));
			}
		catch (SqlNoDataException $e)
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
			catch (SqlNoDataException $e)
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
			catch (SqlNoDataException $e)
				{
				$this->showWarning('Moderator "'.htmlspecialchars($mod).'" nicht gefunden');
				}
			}
		}
	}

protected function sendForm()
	{
	if($this->User->isLevel(User::ADMIN))
		{
		$this->Sql->query
			('
			UPDATE
				boards
			SET
				admin = '.$this->admin.'
			WHERE
				id = '.$this->Board->getId()
			);
		}

	$this->Sql->query
		('
		UPDATE
			boards
		SET
			name = \''.$this->Sql->escapeString($this->Io->getHtml('name')).'\',
			description = \''.$this->Sql->escapeString($this->Markup->toHtml($this->Io->getString('description'))).'\'
		WHERE
			id = '.$this->Board->getId()
		);

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
			Das hilft so aber auch nciht unbedingt viel :-(
		*/
		$this->Sql->query('LOCK TABLES user_group WRITE');
		$groupid = $this->Sql->fetchValue('SELECT MAX(groupid) FROM user_group') + 1;
		$this->Sql->query('INSERT INTO boards SET mods = '.$groupid.' WHERE id = '.$this->Board->getId());
		}
	else
		{
		$groupid = $this->Board->getMods();

		$this->Sql->query
			('
			DELETE FROM
				user_group
			WHERE
				groupid = '.$groupid
			);
		}

	foreach($this->mods as $mod)
		{
		$this->Sql->query
			('
			INSERT INTO
				user_group
			SET
				groupid = '.$groupid.',
				userid = '.$mod
			);
		}

	$this->Sql->query('UNLOCK TABLES');
	}

private function updateAdmins()
	{
	if ($this->Board->getAdmins() == 0 && !empty($this->admins))
		{
		/** FIXME: Gleichzeitiger Zugriff könnte Überscheindung zur Folge haben -> Tabellen sperren */
		$this->Sql->query('LOCK TABLES user_group WRITE');
		$groupid = $this->Sql->fetchValue('SELECT MAX(groupid) FROM user_group') + 1;
		$this->Sql->query('INSERT INTO boards SET admins = '.$groupid.' WHERE id = '.$this->Board->getId());
		}
	else
		{
		$groupid = $this->Board->getAdmins();

		$this->Sql->query
			('
			DELETE FROM
				user_group
			WHERE
				groupid = '.$groupid
			);
		}

	foreach($this->admins as $admin)
		{
		$this->Sql->query
			('
			INSERT INTO
				user_group
			SET
				groupid = '.$groupid.',
				userid = '.$admin
			);
		}

	$this->Sql->query('UNLOCK TABLES');
	}

}


?>