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

		$this->group = $this->Sql->fetchValue
			('
			SELECT
				mods
			FROM
				forums
			WHERE
				id = '.$this->forum.'
				AND boardid = '.$this->Board->getId()
			);
		}
	catch(Exception $e)
		{
		$this->Io->redirect('AdminCats');
		}

	$this->addHidden('forum', $this->forum);

	try
		{
		$mods = $this->Sql->fetchCol
			('
			SELECT
				users.name
			FROM
				users,
				user_group
			WHERE
				user_group.userid = users.id
				AND user_group.groupid = '.$this->group
			);

		$this->addTextArea('mods', 'Moderatoren', implode("\n", $mods), 80, 5);
		}
	catch (SqlNoDataException $e)
		{
		$this->addTextArea('mods', 'Moderatoren', '', 80, 5);
		}
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
			catch (SqlNoDataException $e)
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
		/** FIXME:
			Gleichzeitiger Zugriff könnte Überscheindung zur Folge haben -> Tabellen sperren
			Das hilft so aber auch nciht unbedingt viel :-(
		*/
		$this->Sql->query('LOCK TABLES user_group WRITE, forums WRITE');
		$groupid = $this->Sql->fetchValue('SELECT MAX(groupid) FROM user_group') + 1;
		$this->Sql->query('UPDATE forums SET mods = '.$groupid.' WHERE boardid = '.$this->Board->getId().' AND id = '.$this->forum);
		}
	else
		{
		$groupid = $this->group;

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

}

?>