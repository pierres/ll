<?php


class AdminSettings extends AdminForm{


private $admin = 0;

protected function setForm()
	{
	$this->setValue('title', 'Einstellungen');

	$this->addSubmit('Speichern');

	$this->addText('name', 'Name', htmlspecialchars($this->Board->getName()));
	$this->requires('name');
	$this->setLength('name', 3, 100);

	if($this->User->isLevel(User::ADMIN))
		{
		$this->addText('admin', 'Administrator', htmlspecialchars($this->User->getName($this->Board->getAdmin())));
		$this->requires('admin');
		}
/*
	if($this->User->isUser($this->Board->getAdmin()) || $this->User->isLevel(User::ADMIN))
		{
		$this->addText('admins', 'Administratoren', $this->Board->getAdmins());
		}

	$this->addText('mods', 'Moderatoren', $this->Board->getMods());
*/
	$this->addTextArea('description', 'Beschreibung', $this->UnMarkup->fromHtml($this->Sql->fetchValue('SELECT description FROM boards WHERE id = '.$this->Board->getId())));
	}

protected function checkForm()
	{
	if($this->User->isLevel(User::ADMIN))
		{
		try
			{
			$this->admin = $this->User->getId($this->Io->getString('admin'));
			}
		catch (SqlNoDataException $e)
			{
			$this->showWarning('Administrator nicht gefunden');
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

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminSettings');
	}

}


?>