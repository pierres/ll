<?php

class AdminForumsMods extends AdminForm{



private $forum = 0;
private $mods = array();
private $group;

protected function setForm()
	{
	$this->setValue('title', 'Moderatoren');

	$this->addSubmit('Speichern');

	try
		{
		$this->forum = $this->Io->getInt('forum');

		$stm = $this->DB->prepare
			('
			SELECT
				mods
			FROM
				forums
			WHERE
				id = ?
				AND boardid = ?'
			);
		$stm->bindInteger($this->forum);
		$stm->bindInteger($this->Board->getId());
		$this->group = $stm->getColumn();
		$stm->close();
		}
	catch(Exception $e)
		{
		$stm->close();
		$this->Io->redirect('AdminCats');
		}

	$this->addHidden('forum', $this->forum);

	$mods = array();
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
		$stm->bindInteger($this->group);

		foreach($stm->getColumnSet() as $mod)
			{
			$mods[] = $mod;
			}
		$stm->close();
		}
	catch (DBNoDataException $e)
		{
		$stm->close();
		}

	$this->addTextArea('mods', 'Moderatoren', implode("\n", $mods), 80, 5);
	}

protected function checkForm()
	{
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
	$this->updateMods();

	$this->redirect();
	}

protected function redirect()
	{
	$this->Io->redirect('AdminForumsMods', 'forum='.$this->forum);
	}

private function updateMods()
	{
	if ($this->group == 0 && !empty($this->mods))
		{
		$this->DB->execute('LOCK TABLES user_group WRITE, forums WRITE');
		try
			{
			$groupid = $this->DB->getColumn('SELECT MAX(groupid) FROM user_group') + 1;
			}
		catch (DBNoDataException $e)
			{
			$groupid = 1;
			}

		$stm = $this->DB->prepare
			('
			UPDATE
				forums
			SET
				mods = ?
			WHERE
				boardid = ?
				AND id = ?'
			);
		$stm->bindInteger($groupid);
		$stm->bindInteger($this->Board->getId());
		$stm->bindInteger($this->forum);
		$stm->execute();
		$stm->close();
		}
	else
		{
		$groupid = $this->group;

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

}

?>